<?php

/**
 * Represents a collection of terms related with a post 
 */
class Wordpress_PostTerms {

    /**
     * Post owner of this terms
     * @var Wordpress_Post 
     */
    private $_post;

    /**
     *
     * @var Wordpress_Term[] 
     */
    private $_terms;
    private $_changed = FALSE;

    public function __construct(Wordpress_Post $post, array $terms_data = array()) {
        $this->_post = $post;

        $this->_terms = array();
        foreach ($terms_data as $term_data) {
            $this->_terms[] = new Wordpress_Term($post->site(), $term_data);
        }
    }

    /**
     * Get a value indicating if the terms in this instance have changed
     * @return boolean
     */
    public function changed() {
        return $this->_changed;
    }

    /**
     * Retrieves the list of all terms of this post
     * @return Wordpress_Term[]
     */
    public function all() {
        return $this->_terms;
    }

    /**
     * Add a taxonomy term to the post.
     * @param Wordpress_Term|Wordpress_Term::TYPE_*|string $taxonomy
     * @param string $name
     * @return Wordpress_Term
     */
    public function add($taxonomy, $name = NULL, $description = NULL, $slug = NULL) {
        if ($taxonomy instanceof Wordpress_Term) {
            $term = $taxonomy;
        } else {
            $term = new Wordpress_Term($this->_post->site());
            $term->taxonomy = $taxonomy;
            $term->name = $name;

            if (isset($description))
                $term->description = $description;

            if (isset($slug))
                $term->slug = $slug;
        }

        $this->_changed = TRUE;
        $this->_terms[] = $term;
        return $term;
    }

    public function add_category($name, $description = NULL, $slug = NULL) {
        return $this->add(Wordpress_Term::TYPE_CATEGORY, $name, $description, $slug);
    }

    public function add_tag($name, $description = NULL, $slug = NULL) {
        return $this->add(Wordpress_Term::TYPE_TAG, $name, $description, $slug);
    }

    /**
     * Find a term by name
     * @param string $name
     * @param Wordpress_Term::TYPE_*|string $taxonomy
     * @return Wordpress_Term|FALSE
     */
    public function find_by_name($name, $taxonomy = NULL) {
        foreach ($this->_terms as $post_term) {
            if (!isset($taxonomy) || $post_term->taxonomy == $taxonomy) {
                if (strcasecmp($post_term->name, $name) == 0) {
                    return $post_term;
                }
            }
        }
        return FALSE;
    }

    /**
     * Remove the term from the post
     * @param Wordpress_Term $term
     */
    public function delete(Wordpress_Term $term) {
        foreach ($this->_terms as $key => $post_term) {
            if ($post_term == $term) {
                unset($this->_terms[$key]);
                $this->_changed = TRUE;
                break;
            }
        }
    }

    public function serialize() {
        $terms = array();
        foreach ($this->_terms as $term) {
            if (!$term instanceof Wordpress_Term)
                throw new InvalidArgumentException('The post terms should be an array of Wordpress_Term object');

            //Add the term to the terms array (organized by taxonomy)
            if (!isset($terms[$term->taxonomy]))
                $terms[$term->taxonomy] = array();
            $terms[$term->taxonomy][] = htmlspecialchars($term->name); //Fix for WP bug #24354 http://core.trac.wordpress.org/ticket/24354
        }
        return $terms;
    }

}