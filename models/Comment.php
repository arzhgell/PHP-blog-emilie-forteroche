<?php

class Comment extends AbstractEntity 
{
    private int $idArticle;
    private string $pseudo;
    private string $content;
    private DateTime $dateCreation;
    
    public function getIdArticle(): int 
    {
        return $this->idArticle;
    }

    public function setIdArticle(int $idArticle): void 
    {
        $this->idArticle = $idArticle;
    }

    public function getPseudo(): string 
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): void 
    {
        $this->pseudo = $pseudo;
    }

    public function getContent(): string 
    {
        return $this->content;
    }

    public function setContent(string $content): void 
    {
        $this->content = $content;
    }

    public function getDateCreation(): DateTime 
    {
        return $this->dateCreation;
    }
    
    public function setDateCreation(string|DateTime $dateCreation, string $format = 'Y-m-d H:i:s') : void 
    {
        if (is_string($dateCreation)) {
            $dateCreation = DateTime::createFromFormat($format, $dateCreation);
        }
        $this->dateCreation = $dateCreation;
    }
}