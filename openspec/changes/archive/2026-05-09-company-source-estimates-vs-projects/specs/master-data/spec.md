## ADDED Requirements

### Requirement: Librería de compañías — indicadores E y P

El listado administrativo de compañías (Librería) SHALL mostrar, para cada compañía, de forma visible y consistente con el resto de la UI Metronic, si corresponde el indicador **E** (origen estimados) y/o **P** (asociada a al menos un proyecto), según los datos servidos por el backend.

#### Scenario: Fila con solo E

- **WHEN** el backend indica origen estimados verdadero y asociación a proyecto falsa
- **THEN** la fila MUST mostrar el indicador **E** y MUST NOT mostrar **P** como aplicable

#### Scenario: Fila con solo P

- **WHEN** el backend indica origen estimados falso y asociación a proyecto verdadera
- **THEN** la fila MUST mostrar el indicador **P** y MUST NOT mostrar **E** como aplicable

#### Scenario: Fila con E y P

- **WHEN** el backend indica ambos verdaderos
- **THEN** la fila MUST mostrar ambos indicadores **E** y **P**

#### Scenario: Leyenda o ayuda

- **WHEN** el usuario sitúa el foco o el puntero sobre los indicadores (p. ej. tooltip o texto auxiliar)
- **THEN** MUST poder entenderse que **E** se refiere al origen vía estimados y **P** al vínculo con proyectos
