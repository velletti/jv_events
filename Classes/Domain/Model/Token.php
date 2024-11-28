<?php
namespace JVelletti\JvEvents\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Token extends AbstractEntity
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $token = '';

    /**
     * @var int|null
     */
    protected $feuser = null;

    /**
     * @var string
     */
    protected $license = '';

    /**
     * @var \DateTime|null
     */
    protected $tstamp = null;

    /**
     * @var \DateTime|null
     */
    protected $crdate = null;

    /**
     * @var int|null
     */
    protected $cruserId = null;

    /**
     * @var \DateTime|null
     */
    protected $starttime = null;

    /**
     * @var \DateTime|null
     */
    protected $endtime = null;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var bool
     */
    protected $deleted = false;

    // Getter and Setter for name
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // Getter and Setter for token
    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    // Getter and Setter for feuser
    public function getFeuser(): ?int
    {
        return $this->feuser;
    }

    public function setFeuser(?int $feuser): void
    {
        $this->feuser = $feuser;
    }

    // Getter and Setter for license
    public function getLicense(): string
    {
        return ($this->license ?? 'DEMO' );
    }

    public function setLicense(string $license): void
    {
        $this->license = $license;
    }

    // Getter and Setter for tstamp
    public function getTstamp(): ?\DateTime
    {
        return $this->tstamp;
    }

    public function setTstamp(?\DateTime $tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    // Getter and Setter for crdate
    public function getCrdate(): ?\DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(?\DateTime $crdate): void
    {
        $this->crdate = $crdate;
    }

    // Getter and Setter for cruserId
    public function getCruserId(): ?int
    {
        return $this->cruserId;
    }

    public function setCruserId(?int $cruserId): void
    {
        $this->cruserId = $cruserId;
    }

    // Getter and Setter for starttime
    public function getStarttime(): ?\DateTime
    {
        return $this->starttime;
    }

    public function setStarttime(?\DateTime $starttime): void
    {
        $this->starttime = $starttime;
    }

    // Getter and Setter for endtime
    public function getEndtime(): ?\DateTime
    {
        return $this->endtime;
    }

    public function setEndtime(?\DateTime $endtime): void
    {
        $this->endtime = $endtime;
    }

    // Getter and Setter for hidden
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    // Getter and Setter for deleted
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
}
