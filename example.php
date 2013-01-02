<?php

require_once 'events.php';

/**
 * Just a really quick example on it's usage.
 */

$event = new events;

/**
 * Priority 10. Doing this to show priorities and how they are run in numerical order.
 * In this particular register, we are simply going to lower the password in it's current state
 * <password>_salted~!
 */
$event->register(10, 'password', function($password){
    return strtolower($password);
});


/**
 * First, lets add a salt. Uppercase(to show the next step actually works.
 */
$event->register(1, 'password', function($password){
    return $password . '_SALTED~!';
});

/**
 * Go ahead and hash the password. 99 is after 1 and 10, so it'll be the last thing
 * done to the password.
 */
$event->register(99, 'password', function($password){
    return md5($password);
});


/**
 * Register a "hook". In this case, it's a function that just echos out the password.
 * Pretty basic, but shows how you can use it.
 */
$event->register(100, 'password', function($password){
    echo 'Newly hashed/salted password: ' . $password . "\n";
    /**
     * We need to return the password still!
     * Or do we? That's up to YOU the developer!
     * General rule of thumb,
     * If you "filter" something, always return the value.
     * If you "hook" onto something, it's understood nothing needs to be returned.
     * Use your imagination though :)
     */
    return $password;
});


$password = $event->password('hello_world');

/**
 * Notice how $event->password, where ->password is the name of the trigger?
 * You can also run it like this if you'd like.
 * $password = $event->run('password', 'hello_world');
 */


if($password === md5(strtolower('hello_world_SALTED~!'))){
    echo 'It works!';
}else{
    echo 'Hmmmm .... something broke.';
}


echo "\nFinished...\n";