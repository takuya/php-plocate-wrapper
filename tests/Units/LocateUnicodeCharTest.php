<?php

namespace Tests\PhpPlocateWrapper\Units;

use Tests\PhpPlocateWrapper\TestCase;
use Takuya\PhpPlocateWrapper\LocateWrap;
use Takuya\PhpPlocateWrapper\LocateDbBuilder;
use function Takuya\Helpers\str_rand;
use function Takuya\Helpers\temp_dir;

class LocateUnicodeCharTest extends TestCase {
  
  public function setUp():void {
    parent::setUp();
    $this->tmpdir = temp_dir();
    touch("{$this->tmpdir}/あいう.txt");
    touch("{$this->tmpdir}/sample.txt");
    $this->db = $this->tmpdir.DIRECTORY_SEPARATOR.str_rand(10).'.db';
    $builder = new LocateDbBuilder($this->db, $this->tmpdir,32);
    $builder->build();
  }
  
  public function tearDown():void {
    parent::tearDown();
    file_exists($this->db) && unlink($this->db);
  }
  public function test_plocate_unicode_search() {
    $locate = new LocateWrap($this->db);
    $ret = $locate->search('sample');
    $ret  = iterator_to_array($ret);
    $this->assertEquals('sample.txt',$ret[0]);
    $ret = $locate->search('あいう');
    $ret  = iterator_to_array($ret);
    $this->assertEquals('あいう.txt',$ret[0]);
    
  }
  public function test_plocate_unicode_ignore_case_search() {
    $locate = new LocateWrap($this->db);
    $ret = $locate->search('あいう',true);
    $ret  = iterator_to_array($ret);
    $this->assertEquals('あいう.txt',$ret[0]);
  }
}

