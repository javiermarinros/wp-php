<?php

/**
 * Represents a post in a Wordpress website
 * 
 * @property string $post_id
 * @property string $post_title
 * @property int|string $post_date Unix timestamp or ISO 8601 date
 * @property int|string $post_date_gmt Unix timestamp or ISO 8601 date
 * @property int|string $post_modified Unix timestamp or ISO 8601 date
 * @property int|string $post_modified_gmt Unix timestamp or ISO 8601 date
 * @property string $post_status
 * @property string $post_type
 * @property string $post_format
 * @property string $post_name
 * @property string $post_author
 * @property string $post_password
 * @property string $post_excerpt
 * @property string $post_content
 * @property string $post_parent
 * @property string $post_mime_type
 * @property string $link
 * @property string $guid
 * @property int $menu_order
 * @property string $comment_status
 * @property string $ping_status
 * @property bool $sticky
 * @property struct $post_thumbnail See wp.getMediaItem.
 * @property Wordpress_PostTerms $terms
 * @property array $custom_fields
 * @property array $enclosure
 */
class Wordpress_Post extends Wordpress_Object {
    //Constants

    const TYPE_POST = 'post';
    const TYPE_PAGE = 'page';
    const STATUS_PUBLISHED = 'publish';
    const STATUS_DRAFT = 'draft';
    const STATUS_DELETED = 'deleted';

    public function __construct(Wordpress_Site $site, $data = NULL) {
        parent::__construct($site, $data);

        //Parse terms
        $this->terms = new Wordpress_PostTerms($this, isset($data['terms']) ? $data['terms'] : array());
        $this->_changed = array();

        //Convert dates to Unix Timestamps
        foreach (array('post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt') as $field) {
            if (isset($data[$field])) {
                $date = $data[$field];
                $func = strpos($field, 'gmt') !== FALSE ? 'gmmktime' : 'mktime';
                $this->$field = $func($date->hour, $date->minute, $date->second, $date->month, $date->day, $date->year);
            }
        }
    }

    /**
     * Load a post by its id
     * @param mixed $id
     */
    public function load($id) {
        $new_post = $this->_site->get_post($id);
        $this->_data = $new_post->_data;
        $this->_changed = array();
    }

    protected function _filter($data) {
        if (isset($data['terms'])) {
            if (!($data['terms'] instanceof Wordpress_PostTerms))
                throw new InvalidArgumentException('The post terms should be a Wordpress_PostTerms object');

            $data['terms_names'] = $data['terms']->serialize();
            unset($data['terms']);
        }

        //Convert dates to IXR_Date objects, considering timezone settings
        foreach (array('post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt') as $field) {
            if (isset($data[$field])) {
                $timestamp = $data[$field];
                if (!is_numeric($timestamp))
                    $timestamp = strtotime($timestamp);

                $date = new IXR_Date($timestamp);
                $func = strpos($field, 'gmt') !== FALSE ? 'gmdate' : 'date';
                $date->year = $func('Y', $timestamp);
                $date->month = $func('m', $timestamp);
                $date->day = $func('d', $timestamp);
                $date->hour = $func('H', $timestamp);
                $date->minute = $func('i', $timestamp);
                $date->second = $func('s', $timestamp);
                $date->timezone = '';

                $data[$field] = $date;
            }
        }

        return parent::_filter($data);
    }

    public function is_new() {
        return empty($this->post_id);
    }

    /**
     * Delete the current post
     * @return boolean
     */
    public function delete() {
        if ($this->is_new())
            return TRUE;

        return $this->_site->_query('wp.deletePost', $this->post_id);
    }

    /**
     * Save the current post, and if not exists, create a new one
     * @return boolean
     */
    public function save() {
        $new = empty($this->post_id);

        if ($this->terms->changed())
            $this->_changed[] = 'terms';

        $success = $this->_save('wp.newPost', 'wp.editPost', 'post_id');

        if ($success) {
            //Reload object to get all the new info
            $this->load($this->post_id);
        }

        return $success;
    }

}