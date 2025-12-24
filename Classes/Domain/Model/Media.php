<?php
namespace JVelletti\JvEvents\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * Media
 */
class Media extends AbstractEntity
{

    /**
     * lastmod Date as timestring
     *
     * @var int
     */
    protected $tstamp ;

    protected ?int $_languageUid ;

    /**
     * Name of the media
     *
     * @var string
     */
    protected $name = '';

    /**
     * External Order Now link  of the media
     *
     * @var string
     */
    protected $link = '';


    /**
     * Name UTLR of the media
     *
     * @var string
     */
    protected $slug = '';

    /**
     * Media category
     *
     * @var ObjectStorage<Category>

     */
    protected $mediaCategory = null;


    /**
     * Organizer Id of this Location
     *
     * @var Organizer|null
     */
    protected $organizer = null;


    /**
     * Teaser image
     *
     * @var FileReference|null
     */
    protected $teaserImage = null;

    /**
     * Teaser text
     *
     * @var string
     */
    protected $teaserText = '';

    /**
     * Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * Release date
     *
     * @var \DateTime
     */
    protected $releaseDate = null;

    /**
     * release Date of this media . replacement for releaseDate but FE = for frontend Editing
     *
     * @var string
     */
    protected $releaseDateFE = null;

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->mediaCategory = new ObjectStorage();
    }

    /**
     * Adds a Category
     *
     * @return void
     */
    public function addMediaCategory(Category $mediaCategory)
    {
        $this->mediaCategory->attach($mediaCategory);
    }

    /**
     * Removes a Category
     *
     * @param Category $mediaCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeMediaCategory(Category $mediaCategoryToRemove)
    {
        $this->mediaCategory->detach($mediaCategoryToRemove);
    }

    /**
     * Returns the mediaCategory
     *
     * @return ObjectStorage<Category> $mediaCategory
     */
    public function getMediaCategory()
    {
        return $this->mediaCategory;
    }

    /**
     * Sets the mediaCategory
     *
     * @param ObjectStorage<Category> $mediaCategory
     * @return void
     */
    public function setMediaCategory(ObjectStorage $mediaCategory)
    {
        $this->mediaCategory = $mediaCategory;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    public function getTeaserImage():?FileReference
    {
        return $this->teaserImage;
    }

    /**
     * @param FileReference $teaserImage
     */
    public function setTeaserImage(?FileReference $teaserImage):void
    {
        $this->teaserImage = $teaserImage;
    }

    /**
     * Returns the teaser text
     *
     * @return string
     */
    public function getTeaserText(): string
    {
        return $this->teaserText;
    }

    /**
     * Sets the teaser text
     *
     * @param string $teaserText
     */
    public function setTeaserText(string $teaserText): void
    {
        $this->teaserText = $teaserText;
    }

    /**
     * Returns the description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Returns the release date
     *
     * @return \DateTime|null
     */
    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    /**
     * Sets the release date
     *
     * @param \DateTime|null $releaseDate
     */
    public function setReleaseDate(?\DateTime $releaseDate): void
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * Returns the organizer
     *
     * @return ?Organizer $organizer
     */
    public function getOrganizer()
    {
        if( is_object ($this->organizer)) {
            return $this->organizer;
        }
        return null ;
    }

    /**
     * Sets the organizer
     *
     * @return void
     */
    public function setOrganizer(?Organizer $organizer)
    {
        if( is_object( $organizer )) {
            $this->organizer = $organizer;
        } else {
            $this->organizer = null ;
        }
    }
    /**
     * Returns the link
     *
     * @return string
     */
    public function getLink(): string
    {
        return ($this->link ?? '');
    }
    /**
     * Sets the link
     *
     * @param string $link
     */
    public function setLink(?string $link): void
    {
        $this->link = ($link??'');
    }
    /**
     * @return string
     */
    public function getReleaseDateFE()
    {
        return $this->releaseDateFE;
    }

    /**
     * @param string $releaseDateFE
     */
    public function setReleaseDateFE($releaseDateFE): void
    {
        $this->releaseDateFE = $releaseDateFE;
    }

    public function getTstamp(): int
    {
        return ($this->tstamp ?? 0 );
    }
    public function setTstamp(?int $tstamp): void
    {
        $this->tstamp = ( $tstamp ?? time() );
    }

    public function getLanguageUid(): ?int
    {
        return $this->_languageUid;
    }
    public function setLanguageUid(?int $_languageUid): void
    {
        $this->_languageUid = $_languageUid;
    }

    public function getSlug(): string
    {
        return ($this->slug ?? '');
    }
    public function setSlug(?string $slug): void
    {
        $this->slug = ($slug ?? '');
    }

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }


}