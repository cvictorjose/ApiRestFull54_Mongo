<?php
use App\User;


/**
 * Extract mention from story body
 *
 * @param  string  $body
 * @return array
 */

function extractMentions($body)
{
    try {

        $mentions=array();
        preg_match_all('/@([^(@|\s|\W)]+)/', strip_tags($body), $result);
        if(count($result[1])){
            foreach ($result[1] as $username){
                $u=User::where('username',$username)->first();
                if($u)
                    $mentions[]=$u->_id;
            }
        }
        return $mentions;
    } catch (Exception $e) {
        return array();
    }
}

/**
 * Convert username  to id
 *
 * @param  string  $body
 * @return string
 */

function usernameToId($body)
{
    try {
        preg_match_all('/@([^(@|\s|\W)]+)/', $body, $result);
        if(count($result[1])){
            foreach ($result[1] as $username){
                $u=User::where('username',$username)->first();
                if($u)
                    $body=preg_replace("/@".$username."/", "@".$u->_id, $body);
            }
        }
        return $body;
    } catch (Exception $e) {
        return $body;
    }
}
/**
 * Convert id to username
 *
 * @param  string  $body
 * @return string
 */

function idToUsername($body)
{
    try {
        preg_match_all('/@([^(@|\s|\W)]+)/', $body, $result);
        if(count($result[1])){
            foreach ($result[1] as $id){
                $u=User::find($id);
                if($u)
                    $body=preg_replace("/@".$id."/", "@".$u->username, $body);
            }
        }
        return $body;
    } catch (Exception $e) {
        return $body;
    }
}

/**
 * Extract tag from story body
 *
 * @param  string  $body
 * @return array
 */

function extractTags($body)
{
    try {

        $tags=array();
        preg_match_all('/#([^(#|\s|\W)]+)/', strip_tags($body), $result);
        if(count($result[1])){
            foreach ($result[1] as $tag){
                $tags[]=$tag;
            }
        }
        return $tags;
    } catch (Exception $e) {
        return array();
    }
}


