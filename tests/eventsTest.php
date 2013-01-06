<?php

require_once __DIR__ . '/../src/kcmerrill/events.php';

use kcmerrill\events;

class eventsTest extends PHPUnit_Framework_TestCase {

    var $events;

    public function setUp(){
        $this->events = new events;
    }

    public function tearDown(){
        $this->events = false;
    }

    public function testEventObj(){
        $this->assertTrue(is_object($this->events));
    }

    public function testRun(){
        //Test if we pass through a bunk trigger
        $result = $this->events->run('trigger_does_not_exist', 'RETURNMEASIAM');

        $this->assertEquals('RETURNMEASIAM', $result);

        $this->events->register('concat', function($string){
            return $string.'.';
        });

        //Lower and md5 the string
        $this->events->register('concat', 'strtolower|md5');

        //run it!
        $result = $this->events->run('concat', 'HASHME');
        $this->assertEquals(md5(strtolower('HASHME.')) , $result);

        //Test to make sure our magic call runs too
        $result = $this->events->concat('HASHME');
        $this->assertEquals(md5(strtolower('HASHME.')) , $result);
    }

    public function testUnregister(){
        $this->events->register('changeme', function($string){
            return 'CHANGED!';
        });
        $this->events->register('changeme',array('strtolower', function($string){
            $string = is_string($string) ? $string : '';
            return str_replace('!','',$string);
        }));

        //Make sure our trigger is registered and working
        $result = $this->events->changeme('DOESNTMATTERWHATIPUTHERE');
        $this->assertEquals('changed', $result);

        //Ok, now lets unregister it, and make sure it works.
        $this->events->unRegister('changeme');
        $result = $this->events->changeme('DOESNTMATTERWHATIPUTHERE');
        $this->assertEquals('DOESNTMATTERWHATIPUTHERE', $result);

    }

    public function testDryRun(){
        $this->events->register('dryrun','strtolower|ucwords');
        $this->events->register('dryrun', function($string){
            return str_replace('Should', '**WILL**', $string);
        });
        $dry_run = $this->events->dryrun('THIS SHOULD BE LOWER, EACH WORD SHOULD BE CAPPED', true);
        //Is it a dry run?
        $this->assertTrue(is_array($dry_run));
        $this->assertEquals(3, count($dry_run));
        $this->assertEquals('strtolower', $dry_run[0]);
        $this->assertEquals('ucwords', $dry_run[1]);
        $this->assertEquals('__CALLBACK__', $dry_run[2]);

        //Ok, for good measure, lets test it out again :)
        $result = $this->events->dryrun('THIS SHOULD BE LOWER, EACH WORD SHOULD BE CAPPED');
        $this->assertEquals('This **WILL** Be Lower, Each Word **WILL** Be Capped', $result);
    }
}