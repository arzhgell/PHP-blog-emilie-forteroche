<?php

/**
 * Comment Entity
 */
class Comment extends AbstractEntity 
{
    private int $idArticle;
    private string $pseudo;
    private string $content;
    private DateTime $dateCreation;
    
    /**
     * Getter for the article id.
     * @return int
     */
    public function getIdArticle(): int 
    {
        return $this->idArticle;
    }

    /**
     * Setter for the article id.
     * @param int $idArticle
     * @return void
     */
    public function setIdArticle(int $idArticle): void 
    {
        $this->idArticle = $idArticle;
    }

    /**
     * Getter for the pseudo.
     * @return string
     */
    public function getPseudo(): string 
    {
        return $this->pseudo;
    }

    /**
     * Setter for the pseudo.
     * @param string $pseudo
     * @return void
     */
    public function setPseudo(string $pseudo): void 
    {
        $this->pseudo = $pseudo;
    }

    /**
     * Getter for the content.
     * @return string
     */
    public function getContent(): string 
    {
        return $this->content;
    }

    /**
     * Setter for the content.
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void 
    {
        $this->content = $content;
    }

    /**
     * Getter for the creation date.
     * @return DateTime
     */
    public function getDateCreation(): DateTime 
    {
        return $this->dateCreation;
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
}