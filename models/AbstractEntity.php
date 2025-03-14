<?php

abstract class AbstractEntity 
{
    // Default id is -1 to identify new entities
    protected int $id = -1;

    /**
     * Constructor with optional data for hydration
     */
    public function __construct(array $data = []) 
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hydrates entity from array data
     * Converts underscores to camelCase (e.g., date_creation → setDateCreation)
     */
    protected function hydrate(array $data) : void 
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    /** 
     * Setter for the id.
     * @param int $id
     * @return void
     */
    public function setId(int $id) : void 
    {
        $this->id = $id;
    }

    
    /**
     * Getter for the id.
     * @return int
     */
    public function getId() : int 
    {
        return $this->id;
    }
}