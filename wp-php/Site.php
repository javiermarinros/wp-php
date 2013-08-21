<?php

require 'include/class-IXR.php';

/**
 * Represents a website which uses Wordpress
 */
class Wordpress_Site {

    /**
     * XML RPC client
     * @var IXR_Client 
     */
    protected $_client;
    private $_user, $_password, $_blog_id;

    public function __construct($url, $user, $password, $blog_id = 1) {
        $this->_client = new IXR_Client($url);
        $this->_user = $user;
        $this->_password = $password;
        $this->_blog_id = $blog_id;
    }

    /**
     * Retrieve a post of any registered post type.
     * @param string $id Post ID
     * @return Wordpress_Post[]
     */
    public function get_post($id, $fields = NULL) {
        if (isset($fields))
            $post_data = $this->_query('wp.getPost', $id, $fields);
        else
            $post_data = $this->_query('wp.getPost', $id);

        return new Wordpress_Post($this, $post_data);
    }

    /**
     * Retrieve list of posts of any registered post type.
     * @param Wordpress_Post::TYPE_*|string $post_type
     * @param Wordpress_Post::STATUS_*|string $post_status
     * @param int $number
     * @param int $offset
     * @return Wordpress_Post[]
     */
    public function get_posts($post_type, $post_status, $number = 10, $offset = 0) {
        $response = $this->_query('wp.getPosts', array(
            'post_type' => $post_type,
            'post_status' => $post_status,
            'number' => $number,
            'offset' => $offset,
                ));

        $result = array();
        foreach ($response as $post_data) {
            $result[] = new Wordpress_Post($this, $post_data);
        }
        return $result;
    }

    /**
     * Retrieve a taxonomy term.
     * @param Wordpress_Term::TYPE_*|string $taxonomy
     * @param int $id
     * @return Wordpress_Term
     */
    public function get_term($taxonomy, $id) {
        $term_data = $this->_query('wp.getTerm', $taxonomy, $id);

        return new Wordpress_Term($this, $term_data);
    }

    /**
     * Retrieve list of terms in a taxonomy.
     * @param Wordpress_Term::TYPE_*|string $taxonomy
     * @param int $number
     * @param int $offset
     * @return Wordpress_Term[]
     */
    public function get_terms($taxonomy, $number = 10, $offset = 0, $search = NULL) {
        $filter = array(
            'number' => $number,
            'offset' => $offset,
        );

        if (isset($search))
            $filter['search'] = $search;

        $response = $this->_query('wp.getTerms', $taxonomy, $filter);

        $result = array();
        foreach ($response as $term_data) {
            $result[] = new Wordpress_Term($this, $term_data);
        }
        return $result;
    }

    /**
     * @access private
     * @return array
     * @throws Wordpress_Exceptionç
     */
    public function _query() {
        $args = func_get_args();
        $method = array_shift($args);
        $params = array_merge(array($method, $this->_blog_id, $this->_user, $this->_password), $args);
        if (!call_user_func_array(array($this->_client, 'query'), $params)) {
            throw new Wordpress_Exception($this->_client->getErrorMessage(), $this->_client->getErrorCode());
        }

        return $this->_client->getResponse();
    }

}