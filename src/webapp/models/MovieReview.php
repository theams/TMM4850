<?php

namespace tdt4237\webapp\models;

class MovieReview
{
    const SELECT_BY_ID = "SELECT * FROM moviereviews WHERE id = %s";

    private $id = null;
    private $movieId;
    private $author;
    private $text;

    static $app;

    public function getId()
    {
        return $this->id;
    }

    public function getMovieId()
    {
        return $this->movieId;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setMovieId($id)
    {
        $this->movieId = $id;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    static function make($id, $author, $text)
    {
        $review = new MovieReview();
        $review->id = $id;
        $review->author = $author;
        $review->text = $text;

        return $review;
    }

    /**
     * Insert or save review into db.
     */
    function save()
    {
        $movieId = $this->movieId;
        $author = $this->author;
        $text = $this->text;

        if ($this->id === null) {
            $query = "INSERT INTO moviereviews (movieid, author, text) "
                   . "VALUES (?, ?, ?)";
            $stmt = self::$app->db->prepare($query);
            $stmt->execute(array($movieId,$author,$text));
            return $stmt;
        } else {
            // TODO: Update moviereview here
        }
    }

    static function makeEmpty()
    {
        return new MovieReview();
    }

    /**
     * Fetch all movie reviews by movie id.
     */
    static function findByMovieId($id)
    {
        $query = "SELECT * FROM moviereviews WHERE movieid = ?";
        $stmt = self::$app->db->prepare($query);
        $stmt->execute(array($id));

        $reviews = [];

        foreach ($stmt as $row) {
            $review = self::makeFromRow($row);
            array_push($reviews, $review);
        }

        return $reviews;
    }

    static function makeFromRow($row) {
        $review = self::make(
            $row['id'],
            $row['author'],
            $row['text']
        );

        return $review;
    }
}
MovieReview::$app = \Slim\Slim::getInstance();
