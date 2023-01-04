<?php

namespace Assegai\App\%== __name@pascalize__ ==%;

use Assegai\App\%== __name@pascalize__ ==%\Dto\Create%== __singular@pascalize__ ==%Dto;
use Assegai\App\%== __name@pascalize__ ==%\Dto\Update%== __singular@pascalize__ ==%Dto;
use Assegai\Core\Attributes\Injectable;

#[Injectable]
class %== __name@pascalize__ ==%Service
{
  function create(Create%== __singular@pascalize__ ==%Dto $create%== __singular@pascalize__ ==%Dto)
  {
    return 'This action adds a new %== __singular@lowercase__ ==%';
  }

  function findAll()
  {
    return "This action returns all %== __name@lowercase__ ==%";
  }

  function findOne(int $id)
  {
    return "This action returns a #$id %== __name@lowercase__ ==%";
  }

  function update(int $id, Update%== __singular@pascalize__ ==%Dto $update%== __singular@pascalize__ ==%Dto)
  {
    return "This action updates a #$id %== __singular@lowercase__ ==%";
  }

  function remove(int $id) {
    return "This action removes a #$id %== __singular@lowercase__ ==%";
  }
}