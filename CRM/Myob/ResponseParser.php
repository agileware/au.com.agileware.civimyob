<?php

/**
 * Interface to be implemented by response parsers.
 * Interface CRM_Myob_ResponseParser
 */
interface CRM_Myob_ResponseParser {
  public function parse($content); //Implementation must return an array.

}
