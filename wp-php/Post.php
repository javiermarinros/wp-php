<?php

/**
 * Represents a post in a Wordpress website
 * 
 * @property string $post_id
 * @property string $post_title
 * @property datetime $post_date
 * @property datetime $post_date_gmt
 * @property datetime $post_modified
 * @property datetime $post_modified_gmt
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

        $this->terms = new Wordpress_PostTerms($this, isset($data['terms']) ? $data['terms'] : array());
        $this->_changed = array();
    }

    protected function _filter($data) {
        if (isset($data['terms'])) {
            if (!($data['terms'] instanceof Wordpress_PostTerms))
                throw new InvalidArgumentException('The post terms should be a Wordpress_PostTerms object');

            $data['terms'] = $data['terms']->serialize();
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

        if ($success && $new) {
            //Reload object to get all the new info
            $new_post = $this->_site->get_post($this->post_id);
            $this->_data = $new_post->_data;
            $this->_changed = array();
        }

        return $success;
    }

}