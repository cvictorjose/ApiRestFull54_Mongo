<?php

namespace App\Http\Controllers;


use App\Book;
use Dawson\AmazonECS\AmazonECSFacade as Amazon;
use Exception;
use Illuminate\Http\Request;


class BookController extends Controller
{
    /**
     * Lookup item on amazon platform.
     *
     * @return \Illuminate\Http\Response
     */
    public function amazonLookup($id)
    {
        try {
            if(strlen($id)!=10)
                throw new Exception('ASIN_INVALID',400);
            $book=Book::checkBook($id);
            if(!$book){
                $product = Amazon::search($id)->json();

                if(isset($product['Items']['Request']['Errors']['Error']))
                    throw new Exception('ASIN_INVALID',400);

                $item=$product['Items']['Item'];
                $book=Book::checkBook($id);
                if(!$book){
                    $book=Book::create(array(
                        'ASIN'              => $item['ASIN'],
                        'url'               => $item['DetailPageURL'],
                        'title'             => $item['ItemAttributes']['Title'],
                        'author'            => $item['ItemAttributes']['Author'],
                        'thumb_url'         => $item['MediumImage']['URL'],
                        'cover_photo_url'   => (isset($item['ImageSets']['ImageSet']['HiResImage']['URL']))?$item['ImageSets']['ImageSet']['HiResImage']['URL']:$item['LargeImage']['URL'],
                        'price'             => (isset($item['ItemAttributes']['ListPrice']['FormattedPrice']))?$item['ItemAttributes']['ListPrice']['FormattedPrice']:$item['OfferSummary']['LowestNewPrice']['FormattedPrice'],
                        'feed'              => (object)$item
                    ));
                }
            }
            return $this->createMessage($book->infoSmall(),"200");

        } catch (Exception $e) {
            return $this->createMessageError($e->getMessage(),$e->getCode());
        }
    }

}
