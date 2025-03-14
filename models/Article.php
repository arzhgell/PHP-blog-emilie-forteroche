<?php

/**
 * Article Entity
 */
class Article extends AbstractEntity 
{
    private int $idUser;
    private string $title = "";
    private string $content = "";
    private ?DateTime $dateCreation = null;
    private ?DateTime $dateUpdate = null;  
    private int $viewsCount = 0;

    /**
     * Setter for the user id.
     * @param int $idUser
     */
    public function setIdUser(int $idUser) : void 
    {
        $this->idUser = $idUser;
    }

    /**
     * Getter for the user id.
     * @return int
     */
    public function getIdUser() : int 
    {
        return $this->idUser;
    }

    /**
     * Setter for the title.
     * @param string $title
     */
    public function setTitle(string $title) : void 
    {
        $this->title = $title;
    }

    /**
     * Getter for the title.
     * @return string
     */
    public function getTitle() : string 
    {
        return $this->title;
    }

    /**
     * Setter for the content.
     * @param string $content
     */
    public function setContent(string $content) : void 
    {
        $this->content = $content;
    }

    /**
     * Returns content, optionally truncated to specified length
     */
    public function getContent(int $length = -1) : string 
    {
        if ($length > 0) {
            // Use mb_substr to handle multibyte characters correctly
            $content = mb_substr($this->content, 0, $length);
            if (strlen($this->content) > $length) {
                $content .= "...";
            }
            return $content;
        }
        return $this->content;
    }

    /**
     * Sets creation date, converting string to DateTime if needed
     */
    public function setDateCreation(string|DateTime $dateCreation, string $format = 'Y-m-d H:i:s') : void 
    {
        if (is_string($dateCreation)) {
            $dateCreation = DateTime::createFromFormat($format, $dateCreation);
        }
        $this->dateCreation = $dateCreation;
    }

    /**
     * Getter for the creation date.
     * Thanks to the setter, we are guaranteed to retrieve a DateTime object.
     * @return DateTime
     */
    public function getDateCreation() : DateTime 
    {
        return $this->dateCreation;
    }

    /**
     * Sets update date, converting string to DateTime if needed
     */
    public function setDateUpdate(string|DateTime $dateUpdate, string $format = 'Y-m-d H:i:s') : void 
    {
        if (is_string($dateUpdate)) {
            $dateUpdate = DateTime::createFromFormat($format, $dateUpdate);
        }
        $this->dateUpdate = $dateUpdate;
    }

    /**
     * Getter for the update date.
     * Thanks to the setter, we are guaranteed to retrieve a DateTime object or null
     * if the update date has not been defined.
     * @return DateTime|null
     */
    public function getDateUpdate() : ?DateTime 
    {
        return $this->dateUpdate;
    }

    /**
     * Setter for the view count.
     * @param int $viewsCount
     */
    public function setViewsCount(int $viewsCount) : void 
    {
        $this->viewsCount = $viewsCount;
    }

    /**
     * Getter for the view count.
     * @return int
     */
    public function getViewsCount() : int 
    {
        return $this->viewsCount;
    }

    /**
     * Increments view count by 1
     */
    public function incrementViewsCount() : void 
    {
        $this->viewsCount++;
    }
}