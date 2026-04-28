#!/usr/bin/env python3
"""Elimina validateAdminDto manual y tipa DTOs vía argument resolver (una pasada por fichero)."""

from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

# Bloque estándar: fromHttpRequest + validateAdminDto + if viol -> json 400
BLOCK_FULL = re.compile(
    r"^        \$([a-zA-Z_]\w*) = ((?:App\\\\Dto\\\\Admin\\\\[\w\\\\]+|[\w\\\\]+))::fromHttpRequest\(\$request\);\n"
    r"^        \$viol = \$this->validateAdminDto\(\$this->validator, \$\1, \$this->adminTranslator\);\n"
    r"^        if \(\\count\(\$viol\) > 0\) \{\n"
    r"^            return \$this->json\(\$this->formatAdminValidationFailure\(\$viol\), Response::HTTP_BAD_REQUEST\);\n"
    r"^        \}\n",
    re.M,
)

# Solo validate sin return (legacy)
BLOCK_VALIDATE_ONLY = re.compile(
    r"^        \$([a-zA-Z_]\w*) = ((?:App\\\\Dto\\\\Admin\\\\[\w\\\\]+|[\w\\\\]+))::fromHttpRequest\(\$request\);\n"
    r"^        \$this->validateAdminDto\(\$this->validator, \$\1, \$this->adminTranslator\);\n",
    re.M,
)


def nearest_request_method(text: str, pos: int) -> str | None:
    before = text[:pos]
    funcs = list(re.finditer(r"public function (\w+)\(Request \$request\): JsonResponse", before))
    if not funcs:
        return None
    return funcs[-1].group(1)


def collect_sig_updates(text: str) -> list[tuple[str, str, str]]:
    """Lista de (method_name, dto_short, var_name)."""
    out = []
    for pat in (BLOCK_FULL, BLOCK_VALIDATE_ONLY):
        for m in pat.finditer(text):
            method = nearest_request_method(text, m.start())
            if method is None:
                continue
            var = m.group(1)
            dto = m.group(2).replace("\\\\", "\\")
            out.append((method, dto, var))
    return out


def process(content: str) -> tuple[str, int]:
    sig_updates = collect_sig_updates(content)
    original_len = len(sig_updates)

    content = BLOCK_FULL.sub("", content)
    content = BLOCK_VALIDATE_ONLY.sub("", content)

    content = re.sub(
        r"use App\\Controller\\Admin\\Traits\\AdminValidationResponseTrait;\s*\n",
        "",
        content,
    )
    content = re.sub(r"\n\s*use AdminValidationResponseTrait;\s*\n", "\n", content)

    # Quitar dependencias del constructor (orden habitual)
    content = re.sub(
        r",\s*\n\s*private (?:readonly )?ValidatorInterface \$validator,\s*\n\s*private (?:readonly )?TranslatorInterface \$adminTranslator",
        "",
        content,
        count=1,
    )
    content = re.sub(
        r",\s*\n\s*private ValidatorInterface \$validator,\s*\n\s*private readonly TranslatorInterface \$adminTranslator",
        "",
        content,
        count=1,
    )
    content = re.sub(
        r",\s*\n\s*private readonly ValidatorInterface \$validator,\s*\n\s*private readonly TranslatorInterface \$adminTranslator",
        "",
        content,
        count=1,
    )

    content = re.sub(r"\(\s*,", "(", content)
    content = re.sub(r",\s*\)", ")", content)
    content = re.sub(r"\(\s*\)", "()", content)

    seen = set()
    for method, dto, var in sig_updates:
        key = method
        if key in seen:
            continue
        seen.add(key)
        old = f"public function {method}(Request $request): JsonResponse"
        new = f"public function {method}({dto} ${var}): JsonResponse"
        if content.count(old) != 1:
            continue
        content = content.replace(old, new, 1)

    # Imports ya innecesarios (simple heurística)
    if "ValidatorInterface" not in content:
        content = re.sub(
            r"use Symfony\\Component\\Validator\\Validator\\ValidatorInterface;\s*\n",
            "",
            content,
        )
    if "TranslatorInterface" not in content:
        content = re.sub(
            r"use Symfony\\Contracts\\Translation\\TranslatorInterface;\s*\n",
            "",
            content,
        )
    if (
        "Response::" not in content
        and "RedirectResponse" not in content
        and "BinaryFileResponse" not in content
    ):
        content = re.sub(
            r"use Symfony\\Component\\HttpFoundation\\Response;\s*\n",
            "",
            content,
        )

    return content, original_len


def main() -> None:
    targets = sorted((ROOT / "src/Controller/Admin").glob("*Controller.php"))
    targets = [p for p in targets if p.name != "AbstractAdminController.php"]

    total_updates = 0
    for path in targets:
        raw = path.read_text(encoding="utf-8")
        if "validateAdminDto" not in raw and "AdminValidationResponseTrait" not in raw:
            continue
        new, n = process(raw)
        if new != raw:
            path.write_text(new, encoding="utf-8")
            print(f"{path.relative_to(ROOT)} ({n} bloques)")
            total_updates += n

    print(f"Total bloques procesados (aprox.): {total_updates}")


if __name__ == "__main__":
    main()
