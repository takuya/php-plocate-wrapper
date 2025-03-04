<?php

namespace Takuya\PhpPlocateWrapper;

use Takuya\ProcOpen\ProcOpen;
use Takuya\SystemUtil\Stream\StringIO\StringIO;
use function Takuya\ProcOpen\cmd_exists;

class LocateWrap {
  
  public function __construct( protected $locatedb = null ) {
    cmd_exists('plocate') === false && throw new \RuntimeException('plocate not found.');
  }
  
  /**
   * @param string $keyword
   * @param bool   $no_case A flag determine ignore case. TRUE means ignore case, but if $keyword has UNICODE char, this flag will be ignored.( forced to be FALSE )
   * @param bool   $regexp
   * @return \Generator
   */
  public function search( string $keyword="''", bool $no_case=false, bool $regexp = false ):\Generator {
    $is_ascii_only = fn($string)=>!preg_match('/[^\x00-\x7F]/', $string);
    $no_case = $no_case && $is_ascii_only($keyword);
    $cmd = array_filter([
                          'plocate',
                          $this->locatedb ? "-d" : '',
                          $this->locatedb ?? '',
                          $no_case ? '-i' : '',
                          $regexp ? '--regex' : '',
                          $keyword,
                        ]);
    $proc = new ProcOpen($cmd);
    $proc->enableBuffering();
    $proc->run();
    $ret = $proc->getOutput();
    $sio = new StringIO($ret,'temp/maxmemory:'.(1024*1024*10));
    return $sio->lines();
  }
}