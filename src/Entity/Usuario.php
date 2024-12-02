<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Usuario
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioRepository")
 */
class Usuario implements UserInterface, EquatableInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $usuarioId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=false)
     */
    private $apellidos;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=50, nullable=false)
     */
    private $telefono;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=true)
     */
    private $habilitado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var Rol
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Rol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rol_id", referencedColumnName="rol_id")
     * })
     */
    private $rol;

    /**
     * Get usuarioId
     *
     * @return integer
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Usuario
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set apellidos
     *
     * @param string $apellidos
     * @return Usuario
     */
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    /**
     * Get apellidos
     *
     * @return string
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Usuario
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param string $telefono
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Usuario
     */
    public function setContrasenna($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getContrasenna()
    {
        return $this->password;
    }

    /**
     * Set habilitado
     *
     * @param boolean $habilitado
     * @return Usuario
     */
    public function setHabilitado($habilitado)
    {
        $this->habilitado = $habilitado;

        return $this;
    }

    /**
     * Get habilitado
     *
     * @return boolean
     */
    public function getHabilitado()
    {
        return $this->habilitado;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Set rol
     *
     * @param Rol $rol
     * @return Usuario
     */
    public function setRol(Rol $rol = null)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return Rol
     */
    public function getRol()
    {
        return $this->rol;
    }

    public function __toString()
    {
        return $this->getNombre();
    }

    public function getNombreCompleto()
    {
        return $this->nombre . " " . $this->apellidos;
    }

    /*
     * Implementation of UserInterface
     */

    /**
     * Set usuarioid
     *
     * @param string $usuario_id
     */
    public function setUsuarioId($usuario_id)
    {
        $this->usuarioId = $usuario_id;
    }

    public function equals(UserInterface $user)
    {
        return $user->getUsername() == $this->getEmail();
    }

    public function eraseCredentials()
    {

    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        if ($this->rol->getRolId() == 1)
            return array('ROLE_ADMIN');
        else
            return array('ROLE_USER');
    }

    public function getSalt()
    {
        return false;
    }

    public function getUsername()
    {

        return $this->email;
    }

    public function __serialize(): array
    {
        return [
            'usuarioId' => $this->usuarioId,
            'email' => $this->email,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->setUsuarioId($data['usuarioId']);
        $this->setEmail($data['email']);
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        return $user->getUsername() == $this->getEmail();
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isAdministrador()
    {
        return ($this->rol->getRolId() == 1) ? true : false;
    }

    public function isUser()
    {
        return ($this->rol->getRolId() == 2) ? true : false;
    }

}