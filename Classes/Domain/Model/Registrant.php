<?php
namespace JVE\JvEvents\Domain\Model;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jörg velletti <jVelletti@allplan.com>, Allplan GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Registrant
 */
class Registrant extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
	/**
	 * uid
	 *
	 * @var int
	 */
	protected $uid  ;

	/**
	 * crdate
	 *
	 * @var int
	 */
	protected $crdate  ;


	/**
	 * created
	 *
	 * @var int
	 */
	protected $created  ;

    /**
     * Access Start Time of this event (default TYP3 Field )
     * @var \DateTime
     */
    protected $starttime ;

    /**
     * Access End Time of this event (default TYP3 Field )
     * @var \DateTime
     */
    protected $endtime ;

	/**
	 * event
	 *
	 * @var int
	 */
	protected $event = 0 ;

	/**
	 * event
	 *
	 * @var string
	 */
	protected $otherEvents = '' ;

    /**
     * response from citrix webservice
     *
     * @var string
     */
    protected $citrixResponse = '' ;


    /**
     * response from Hubspot webservice
     *
     * @var string
     */
    protected $hubspotResponse = '' ;


	/**
	 * layoutRegister
	 *
	 * @var string
	 */
	protected $layoutRegister = '';

    /**
     * firstName
     *
     * @var string
     */
    protected $firstName = '';

	/**
	 * title
	 *
	 * @var string
	 */
	protected $title = '';


	/**
     * lastName
     *
     * @var string
     */
    protected $lastName = '';
    
    /**
     * email
     *
     * @var string
     */
    protected $email = '';
    
    /**
     * gender
     *
     * @var string
     */
    protected $gender = '';

	/**
	 * hidden
	 *
	 * @var int
	 */
	protected $hidden = 0;

	/**
	 * $confirmed
	 *
	 * @var int
	 */
	protected $confirmed = 0;


	/**
     * company
     *
     * @var string
     */
    protected $company = '';

	/**
	 * company
	 *
	 * @var string
	 */
	protected $company2 = '';

    /**
     * department
     *
     * @var string
     */
    protected $department = '';

	/**
	 * department
	 *
	 * @var string
	 */
	protected $department2 = '';


	/**
     * streetAndNr
     *
     * @var string
     */
    protected $streetAndNr = '';

	/**
	 * streetAndNr
	 *
	 * @var string
	 */
	protected $streetAndNr2 = '';

    /**
     * zip
     *
     * @var string
     */
    protected $zip = '';

	/**
	 * zip
	 *
	 * @var string
	 */
	protected $zip2 = '';

    /**
     * city
     *
     * @var string
     */
    protected $city = '';

	/**
	 * city
	 *
	 * @var string
	 */
	protected $city2 = '';
    
    /**
     * country
     *
     * @var string
     */
    protected $country = '';

	/**
	 * country
	 *
	 * @var string
	 */
	protected $country2 = '';


	/**
     * language
     *
     * @var string
     */
    protected $language = '';
    
    /**
     * phone
     *
     * @var string
     */
    protected $phone = '';
    
    /**
     * additionalInfo
     *
     * @var string
     */
    protected $additionalInfo = '';
    
    /**
     * privacy
     *
     * @var bool
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $privacy = false;
    
    /**
     * newsletter
     *
     * @var bool
     */
    protected $newsletter = false;
    
    /**
     * customerId
     *
     * @var string
     */
    protected $customerId = '';
    
    /**
     * profession
     *
     * @var string
     */
    protected $profession = '';
    
    /**
     * recall
     *
     * @var bool
     */
    protected $recall = false;
    
    /**
     * contactId
     *
     * @var string
     */
    protected $contactId = '';
    
    /**
     * username
     *
     * @var string
     */
    protected $username = '';
    
    /**
     * more1
     *
     * @var string
     */
    protected $more1 = '';
    
    /**
     * more2
     *
     * @var string
     */
    protected $more2 = '';
    
    /**
     * more3
     *
     * @var string
     */
    protected $more3 = '';
    
    /**
     * more4
     *
     * @var string
     */
    protected $more4 = '';
    
    /**
     * more5bool
     *
     * @var bool
     */
    protected $more5bool = false;
    
    /**
     * more6int
     *
     * @var int
     */
    protected $more6int = 0;
    
    /**
     * more7date
     *
     * @var \DateTime
     */
    protected $more7date = null;
    
    /**
     * more8file
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $more8file = null;
    
    /**
     * password
     *
     * @var string
     */
    protected $password = '';


	/**
	 * hotprice Hidden Input, used as HoneyPot to find spammers !
	 *
	 * @var string
	 */
	protected $hotprice  ;

	/**
	 * fingerprint Render a fingerprint from users env to find spammers
	 *
	 * @var string
	 */
	protected $fingerprint  ;

	/**
	 * startReg Rendering Start Time of the Form - to find bots
	 *
	 * @var string
	 */
	protected $startReg  ;

	/**
	 * @return string
	 */
	public function getHotprice() {
		return $this->hotprice;
	}

	/**
	 * @param string $hotprice
	 */
	public function setHotprice($hotprice) {
		$this->hotprice = $hotprice;
	}

	/**
	 * @return string
	 */
	public function getFingerprint() {
		return $this->fingerprint;
	}

	/**
	 */
	public function setFingerprint() {

		$fingerPrint = strtolower( $this->getEmail() . $this->getFirstName() . $this->getLastName() . $this->getEvent() );
		$fingerPrint = md5( $fingerPrint) ;
		$this->fingerprint = $fingerPrint;
	}

	/**
	 * @return string
	 */
	public function getStartReg() {
		return $this->startReg;
	}

	/**
	 * @param string $startReg
	 */
	public function setStartReg($startReg) {
		$this->startReg = $startReg;
	}

	/**
	 * @return int
	 */
	public function getCrdate() {
		return $this->crdate;
	}

	/**
	 * @param int $crdate
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * @return int
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @param int $created
	 */
	public function setCreated($created) {
		$this->created = $created;
	}







	/**
	 * @return int
	 */
	public function getEvent() {
		return $this->event;
	}

	/**
	 * @param int $event
	 */
	public function setEvent($event) {
		$this->event = $event;
	}

	/**
	 * @return string
	 */
	public function getLayoutRegister() {
		return $this->layoutRegister;
	}

	/**
	 * @param string $layoutRegister
	 */
	public function setLayoutRegister($layoutRegister) {
		$this->layoutRegister = $layoutRegister;
	}






    /**
     * Returns the firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }
    
    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }
    
    /**
     * Returns the lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }
    
    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
    
    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
    
    /**
     * Returns the gender
     *
     * @return string $gender
     */
    public function getGender()
    {
        return $this->gender;
    }
    
    /**
     * Sets the gender
     *
     * @param string $gender
     * @return void
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }
    
    /**
     * Returns the company
     *
     * @return string $company
     */
    public function getCompany()
    {
        return $this->company;
    }
    
    /**
     * Sets the company
     *
     * @param string $company
     * @return void
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }
    
    /**
     * Returns the department
     *
     * @return string $department
     */
    public function getDepartment()
    {
        return $this->department;
    }
    
    /**
     * Sets the department
     *
     * @param string $department
     * @return void
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }
    
    /**
     * Returns the streetAndNr
     *
     * @return string $streetAndNr
     */
    public function getStreetAndNr()
    {
        return $this->streetAndNr;
    }
    
    /**
     * Sets the streetAndNr
     *
     * @param string $streetAndNr
     * @return void
     */
    public function setStreetAndNr($streetAndNr)
    {
        $this->streetAndNr = $streetAndNr;
    }
    
    /**
     * Returns the zip
     *
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
    }
    
    /**
     * Sets the zip
     *
     * @param string $zip
     * @return void
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
    
    /**
     * Returns the city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }
    
    /**
     * Returns the country
     *
     * @return string $country
     */
    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * Sets the country
     *
     * @param string $country
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }
    
    /**
     * Returns the language
     *
     * @return string $language
     */
    public function getLanguage()
    {
        return $this->language;
    }
    
    /**
     * Sets the language
     *
     * @param string $language
     * @return void
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }
    
    /**
     * Returns the phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * Sets the phone
     *
     * @param string $phone
     * @return void
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
    
    /**
     * Returns the additionalInfo
     *
     * @return string $additionalInfo
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }
    
    /**
     * Sets the additionalInfo
     *
     * @param string $additionalInfo
     * @return void
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;
    }
    
    /**
     * Returns the privacy
     *
     * @return bool $privacy
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }
    
    /**
     * Sets the privacy
     *
     * @param bool $privacy
     * @return void
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }
    
    /**
     * Returns the boolean state of privacy
     *
     * @return bool
     */
    public function isPrivacy()
    {
        return $this->privacy;
    }
    
    /**
     * Returns the newsletter
     *
     * @return bool $newsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }
    
    /**
     * Sets the newsletter
     *
     * @param bool $newsletter
     * @return void
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }
    
    /**
     * Returns the boolean state of newsletter
     *
     * @return bool
     */
    public function isNewsletter()
    {
        return $this->newsletter;
    }
    
    /**
     * Returns the customerId
     *
     * @return string $customerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }
    
    /**
     * Sets the customerId
     *
     * @param string $customerId
     * @return void
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }
    
    /**
     * Returns the profession
     *
     * @return string $profession
     */
    public function getProfession()
    {
        return $this->profession;
    }
    
    /**
     * Sets the profession
     *
     * @param string $profession
     * @return void
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }
    
    /**
     * Returns the recall
     *
     * @return bool $recall
     */
    public function getRecall()
    {
        return $this->recall;
    }
    
    /**
     * Sets the recall
     *
     * @param bool $recall
     * @return void
     */
    public function setRecall($recall)
    {
        $this->recall = $recall;
    }
    
    /**
     * Returns the boolean state of recall
     *
     * @return bool
     */
    public function isRecall()
    {
        return $this->recall;
    }
    
    /**
     * Returns the contactId
     *
     * @return string $contactId
     */
    public function getContactId()
    {
        return $this->contactId;
    }
    
    /**
     * Sets the contactId
     *
     * @param string $contactId
     * @return void
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
    }
    
    /**
     * Returns the username
     *
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Sets the username
     *
     * @param string $username
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    /**
     * Returns the more1
     *
     * @return string $more1
     */
    public function getMore1()
    {
        return $this->more1;
    }
    
    /**
     * Sets the more1
     *
     * @param string $more1
     * @return void
     */
    public function setMore1($more1)
    {
        $this->more1 = $more1;
    }
    
    /**
     * Returns the more2
     *
     * @return string $more2
     */
    public function getMore2()
    {
        return $this->more2;
    }
    
    /**
     * Sets the more2
     *
     * @param string $more2
     * @return void
     */
    public function setMore2($more2)
    {
        $this->more2 = $more2;
    }
    
    /**
     * Returns the more3
     *
     * @return string $more3
     */
    public function getMore3()
    {
        return $this->more3;
    }
    
    /**
     * Sets the more3
     *
     * @param string $more3
     * @return void
     */
    public function setMore3($more3)
    {
        $this->more3 = $more3;
    }
    
    /**
     * Returns the more4
     *
     * @return string $more4
     */
    public function getMore4()
    {
        return $this->more4;
    }
    
    /**
     * Sets the more4
     *
     * @param string $more4
     * @return void
     */
    public function setMore4($more4)
    {
        $this->more4 = $more4;
    }
    
    /**
     * Returns the more5bool
     *
     * @return bool $more5bool
     */
    public function getMore5bool()
    {
        return $this->more5bool;
    }
    
    /**
     * Sets the more5bool
     *
     * @param string $more5bool
     * @return void
     */
    public function setMore5bool($more5bool)
    {
        $this->more5bool = $more5bool;
    }
    
    /**
     * Returns the boolean state of more5bool
     *
     * @return bool
     */
    public function isMore5bool()
    {
        return $this->more5bool;
    }
    
    /**
     * Returns the more6int
     *
     * @return int $more6int
     */
    public function getMore6int()
    {
        return $this->more6int;
    }
    
    /**
     * Sets the more6int
     *
     * @param string $more6int
     * @return void
     */
    public function setMore6int($more6int)
    {
        $this->more6int = $more6int;
    }
    
    /**
     * Returns the more7date
     *
     * @return \DateTime $more7date
     */
    public function getMore7date()
    {
        return $this->more7date;
    }
    
    /**
     * Sets the more7date
     *
     * @param \DateTime $more7date
     * @return void
     */
    public function setMore7date(\DateTime $more7date)
    {
        $this->more7date = $more7date;
    }
    
    /**
     * Returns the more8file
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $more8file
     */
    public function getMore8file()
    {
        return $this->more8file;
    }
    
    /**
     * Sets the more8file
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $more8file
     * @return void
     */
    public function setMore8file(\TYPO3\CMS\Extbase\Domain\Model\FileReference $more8file)
    {
        $this->more8file = $more8file;
    }
    
    /**
     * Returns the password
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Sets the password
     *
     * @param string $password
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

	/**
	 * @return int
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * @param int $hidden
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}

	/**
	 * @return int
	 */
	public function getConfirmed() {
		return $this->confirmed;
	}

	/**
	 * @param int $confirmed
	 */
	public function setConfirmed($confirmed) {
		$this->confirmed = $confirmed;
	}

	/**
	 * @return string
	 */
	public function getOtherEvents()
	{
		return $this->otherEvents;
	}

	/**
	 * @param string $otherEvents
	 */
	public function setOtherEvents($otherEvents)
	{
		$this->otherEvents = $otherEvents;
	}

	/**
	 * @return string
	 */
	public function getCompany2()
	{
		return $this->company2;
	}

	/**
	 * @param string $company2
	 */
	public function setCompany2($company2)
	{
		$this->company2 = $company2;
	}

	/**
	 * @return string
	 */
	public function getDepartment2()
	{
		return $this->department2;
	}

	/**
	 * @param string $department2
	 */
	public function setDepartment2($department2)
	{
		$this->department2 = $department2;
	}

	/**
	 * @return string
	 */
	public function getStreetAndNr2()
	{
		return $this->streetAndNr2;
	}

	/**
	 * @param string $streetAndNr2
	 */
	public function setStreetAndNr2($streetAndNr2)
	{
		$this->streetAndNr2 = $streetAndNr2;
	}

	/**
	 * @return string
	 */
	public function getZip2()
	{
		return $this->zip2;
	}

	/**
	 * @param string $zip2
	 */
	public function setZip2($zip2)
	{
		$this->zip2 = $zip2;
	}

	/**
	 * @return string
	 */
	public function getCity2()
	{
		return $this->city2;
	}

	/**
	 * @param string $city2
	 */
	public function setCity2($city2)
	{
		$this->city2 = $city2;
	}

	/**
	 * @return string
	 */
	public function getCountry2()
	{
		return $this->country2;
	}

	/**
	 * @param string $country2
	 */
	public function setCountry2($country2)
	{
		$this->country2 = $country2;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

    /**
     * @return string
     */
    public function getCitrixResponse()
    {
        return $this->citrixResponse;
    }

    /**
     * @param string $citrixResponse
     */
    public function setCitrixResponse($citrixResponse)
    {
        $this->citrixResponse = $citrixResponse;
    }

    /**
     * @return string
     */
    public function getHubspotResponse()
    {
        return $this->hubspotResponse;
    }

    /**
     * @param string $hubspotResponse
     */
    public function setHubspotResponse($hubspotResponse)
    {
        $this->hubspotResponse = $hubspotResponse;
    }

    /**
     * @return int
     */
    public function getStarttime(): int
    {
        return $this->starttime;
    }

    /**
     * @param int $starttime
     */
    public function setStarttime(int $starttime)
    {
        $this->starttime = $starttime;
    }

    /**
     * @return int
     */
    public function getEndtime(): int
    {
        return $this->endtime;
    }

    /**
     * @param int $endtime
     */
    public function setEndtime(int $endtime)
    {
        $this->endtime = $endtime;
    }


}