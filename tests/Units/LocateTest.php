<?php

namespace Tests\PhpPlocateWrapper\Units;

use Tests\PhpPlocateWrapper\TestCase;
use Takuya\PhpPlocateWrapper\LocateWrap;
use Takuya\PhpPlocateWrapper\LocateDbBuilder;
use function Takuya\Helpers\str_rand;

class LocateTest extends TestCase {
  
  public function setUp():void {
    parent::setUp();
    $path = realpath(__DIR__.'/../../');
    $this->db = sys_get_temp_dir().DIRECTORY_SEPARATOR.str_rand(10).'.db';
    $builder = new LocateDbBuilder($this->db, $path,32);
    $builder->build();
  }
  
  public function tearDown():void {
    parent::tearDown();
    file_exists($this->db) && unlink($this->db);
  }
  public function test_plocate_search() {
    $locate = new LocateWrap($this->db);
    $ret = $locate->search('composer.json');
    $ret = iterator_to_array($ret);
    $this->assertEquals('composer.json',$ret[0]);
  }
  public function test_plocate_regex_search() {
    $locate = new LocateWrap($this->db);
    $ret = $locate->search('^src',false,true);
    foreach ($ret as $item) {
      $this->assertMatchesRegularExpression('/^src/',$item);
    }
  }
  public function test_plocate_ignorecase_search() {
    $locate = new LocateWrap($this->db);
    $ret = $locate->search('ComposER',true);
    $ret = iterator_to_array($ret);
    $this->assertStringStartsWith('composer.',$ret[0]);
  }
}

