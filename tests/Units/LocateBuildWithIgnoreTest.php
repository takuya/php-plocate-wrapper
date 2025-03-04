<?php

namespace Tests\PhpPlocateWrapper\Units;

use Tests\PhpPlocateWrapper\TestCase;
use Takuya\PhpPlocateWrapper\LocateWrap;
use Takuya\PhpPlocateWrapper\LocateDbBuilder;
use function Takuya\Helpers\str_rand;

class LocateBuildWithIgnoreTest extends TestCase {
  
  public function setUp():void {
    parent::setUp();
    $this->path = realpath(__DIR__.'/../../');
    $this->db = sys_get_temp_dir().DIRECTORY_SEPARATOR.str_rand(10).'.db';
  }
  
  public function tearDown():void {
    parent::tearDown();
    file_exists($this->db) && unlink($this->db);
  }
  public function test_plocate_build_with_ignore_list() {
    $builder = new LocateDbBuilder($this->db, $this->path,32,'no');
    $builder->addIgnore('composer');
    $builder->addIgnore('vendor');
    $builder->addIgnore('^\.idea');
    $builder->addIgnore('^\.git');
    $builder->build();
    $locate = new LocateWrap($this->db);
    foreach(['composer','vendor','.idea','.git'] as $word ){
      $ret = $locate->search($word);
      $ret = iterator_to_array($ret);
      $this->assertEmpty($ret);
    }
    $ret = $locate->search('.',false,true);
    $ret = iterator_to_array($ret);
    $this->assertNotEmpty($ret);
  }
  public function test_plocate_build_add_ignore_with_delim() {
    $builder = new LocateDbBuilder($this->db, $this->path,32,'no');
    $patterns = ['/composer/','|composer|','#^.+\.json$#'];
    foreach ($patterns as $pattern){
      try {
        $builder->addIgnore($pattern);
      }catch(\InvalidArgumentException $e){
        $this->assertStringContainsString('delim',$e->getMessage());
      }
    }
  }
}

