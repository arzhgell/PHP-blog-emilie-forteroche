<?php

class Article extends AbstractEntity 
{
    private int $idUser;
    private string $title = "";
    private string $content = "";
    private ?DateTime $dateCreation = null;
    private ?DateTime $dateUpdate = null;  
    private int $viewsCount = 0;

    public function setIdUser(int $idUser) : void 
    {
        $this->idUser = $idUser;
    }

    public function getIdUser() : int 
    {
        return $this->idUser;
    }

    public function setTitle(string $title) : void 
    {
        $this->title = $title;
    }

    public function getTitle() : string 
    {
        return $this->title;
    }

    public function setContent(string $content) : void 
    {
        $this->content = $content;
    }

    public function getContent(int $length = -1) : string 
    {
        if ($length > 0) {
            $content = mb_substr($this->content, 0, $length);
            if (strlen($this->content) > $length) {
                $content .= "...";
            }
            return $content;
        }
        return $this->content;
    }

    public function setDateCreation(string|DateTime|null $dateCreation, string $format = 'Y-m-d H:i:s') : void 
    {
        if ($dateCreation === null) {
            $this->dateCreation = null;
            return;
        }
        
        if (is_string($dateCreation)) {
            $dateCreation = DateTime::createFromFormat($format, $dateCreation);
        }
        $this->dateCreation = $dateCreation;
    }

    public function getDateCreation() : ?DateTime 
    {
        return $this->dateCreation;
    }

    public function setDateUpdate(string|DateTime|null $dateUpdate, string $format = 'Y-m-d H:i:s') : void 
    {
        if ($dateUpdate === null) {
            $this->dateUpdate = null;
            return;
        }
        
        if (is_string($dateUpdate)) {
            $dateUpdate = DateTime::createFromFormat($format, $dateUpdate);
        }
        $this->dateUpdate = $dateUpdate;
    }

    public function getDateUpdate() : ?DateTime 
    {
        return $this->dateUpdate;
    }

    public function setViewsCount(int $viewsCount) : void 
    {
        $this->viewsCount = $viewsCount;
    }

    public function getViewsCount() : int 
    {
        return $this->viewsCount;
    }

    public function incrementViewsCount() : void 
    {
        $this->viewsCount++;
    }
}