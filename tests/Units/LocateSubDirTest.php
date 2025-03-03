<?php

namespace Tests\PhpPlocateWrapper\Units;

use Tests\PhpPlocateWrapper\TestCase;
use Takuya\PhpPlocateWrapper\LocateWrap;
use Takuya\PhpPlocateWrapper\LocateDbBuilder;
use function Takuya\Helpers\str_rand;

class LocateSubDirTest extends TestCase {
  
  public function setUp():void {
    parent::setUp();
    $this->db = sys_get_temp_dir().DIRECTORY_SEPARATOR.str_rand(10).'.db';
  }
  
  public function tearDown():void {
    parent::tearDown();
    file_exists($this->db) && unlink($this->db);
  }
  public function test_plocate_sub_dir_search() {
    $path = realpath(__DIR__.'/../../vendor');
    $builder = new LocateDbBuilder($this->db, $path,32,'no');
    $builder->build();
    $locate = new LocateWrap($this->db);
    $ret = $locate->search('autoload');
    $ret  = iterator_to_array($ret);
    $this->assertEquals('autoload.php',$ret[0]);
    
  }
}

