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
 * @property Wordpress_Term[] $terms
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

    protected function _load($data) {
        if (isset($data['terms'])) {
            $terms = array();
            foreach ($data['terms'] as $term_data) {
                $terms[] = new Wordpress_Term($this->_site, $term_data);
            }
            $data['terms'] = $terms;
        }
        parent::_load($data);
    }

    protected function _filter($data) {
        if (isset($data['terms'])) {
            $terms = array();
            foreach ($data['terms'] as $term) {
                if (!$term instanceof Wordpress_Term)
                    throw new InvalidArgumentException('The post terms should be an array of Wordpress_Term object');
                if (!isset($terms[$term->taxonomy]))
                    $terms[$term->taxonomy] = array();
                $terms[$term->taxonomy][] = $term->term_id;
            }
            $data['terms'] = $terms;
        }

        return parent::_filter($data);
    }

    /**
     * Delete the current post
     * @return boolean
     */
    public function delete() {
        return $this->_site->_query('wp.deletePost', $this->post_id);
    }

    /**
     * Save the current post, and if not exists, create a new one
     * @return boolean
     */
    public function save() {
        $new = empty($this->post_id);

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