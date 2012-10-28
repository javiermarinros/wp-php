<?php

abstract class Wordpress_Object {

    /**
     * Website owner of this object
     * @var Wordpress_Site
     */
    protected $_site;
    protected $_changed = array();
    protected $_data;

    public function __construct(Wordpress_Site $site, $data = NULL) {
        $this->_site = $site;

        //Load data content into this object
        if (isset($data)) {
            $this->_load($data);
        }
    }
    
    protected function _load($data){
            $this->_data = $data;
    }
    
    protected function _filter($data){
        return $data;
    }

    public function __get($name) {
        return $this->_data[$name];
    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
        $this->_changed[] = $name;
    }

    public function __isset($name) {
        return isset($this->_data[$name]);
    }

    public function __unset($name) {
        unset($this->_data [$name]);
    }

    /**
     * Gets the information of this object in array format
     * @return array
     */
    public function data() {
        return $this->_data;
    }

    /**
     * Save the changes made to the current object
     */
    public abstract function save();

    protected function _save($new_method, $edit_method, $id_field) {
        $content = array();
        foreach ($this->_changed as $property) {
            $content[$property] = $this->$property;
        }
      $content=  $this->_filter($content);
      
        if (!isset($this->$id_field)) {
            //Create
            $id = $this->_site->_query($new_method, $content);
            if ($id) {
                $this->$id_field = $id;
            }
            $result = (bool) $id;
        } else {
            //Update
            $result = $this->_site->_query($edit_method, $this->$id_field, $content);
        }

        $this->_changed = array();
        return $result;
    }

    /**
     * Delete the current object
     */
    public abstract function delete();
}