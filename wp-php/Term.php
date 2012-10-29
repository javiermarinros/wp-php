<?php

/**
 * Represents a term (category, tag, etc.) in a Wordpress website
 * 
 * @property string $term_id
 * @property string $name
 * @property string $slug
 * @property string $term_group
 * @property string $term_taxonomy_id
 * @property string $taxonomy
 * @property string $description
 * @property string $parent
 * @property int $count
 */
class Wordpress_Term extends Wordpress_Object {
    //Constants

    const TYPE_CATEGORY = 'category';
    const TYPE_TAG = 'post_tag';

    public function is_new() {
        return empty($this->term_id);
    }

    /**
     * Delete the current term
     * @return boolean
     */
    public function delete() {
        if ($this->is_new())
            return TRUE;

        return $this->_site->_query('wp.deleteTerm', $this->taxonomy, $this->term_id);
    }

    /**
     * Save the current termn, and if not exists, create a new one
     * @return boolean
     */
    public function save() {

        $new = empty($this->term_id);

        $success = $this->_save('wp.newTerm', 'wp.editTerm', 'term_id');

        if ($success && $new) {
            //Reload object to get all the new info
            $new_term = $this->_site->get_term($this->taxonomy, $this->term_id);
            $this->_data = $new_term->_data;
            $this->_changed = array();
        }

        return $success;
    }

}