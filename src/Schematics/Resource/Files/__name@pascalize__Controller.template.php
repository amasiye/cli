<?php

namespace Assegai\App\%== __name@pascalize__ ==%;

use Assegai\App\%== __name@pascalize__ ==%\Dto\Create%== __singular@pascalize__ ==%Dto;
use Assegai\App\%== __name@pascalize__ ==%\Dto\Update%== __singular@pascalize__ ==%Dto;
use Assegai\Core\Attributes\Controller;
use Assegai\Core\Attributes\Http\Body;
use Assegai\Core\Attributes\Http\Delete;
use Assegai\Core\Attributes\Http\Get;
use Assegai\Core\Attributes\Http\Post;
use Assegai\Core\Attributes\Http\Put;
use Assegai\Core\Attributes\Param;
use Assegai\Core\Attributes\Req;
use Assegai\Core\Http\Requests\Request;

#[Controller('%== __name@snakeize__ ==%')]
class %== __name@pascalize__ ==%Controller
{
  public function __construct(private readonly %== __name@pascalize__ ==%Service $%== __name@camelize__ ==%Service)
  {
  }

  /**
   * @throws Exception
   */
  #[Post]
  public function create(#[Body] Create%== __singular@pascalize__ ==%Dto $create%== __singular@pascalize__ ==%Dto)
  {
    return $this->%== __name@camelize__ ==%Service->create($create%== __singular@pascalize__ ==%Dto);
  }

  #[Get]
  public function findAll(#[Req] Request $request)
  {
    return $this->%== __name@camelize__ ==%Service->findAll();
  }

  #[Get(':id')]
  public function findOne(#[Param('id')] int $id)
  {
    return $this->%== __name@camelize__ ==%Service->findOne($id);
  }

  #[Put(':id')]
  public function update(
    #[Param('id')] string $id,
    #[Body] Update%== __singular@pascalize__ ==%Dto $update%== __singular@pascalize__ ==%Dto
  )
  {
    return $this->%== __name@camelize__ ==%Service->update($id, update%== __singular@pascalize__ ==%Dto: $update%== __singular@pascalize__ ==%Dto);
  }

  #[Delete(':id')]
  public function remove(#[Param('id')] string $id)
  {
    return $this->%== __name@camelize__ ==%Service->remove($id);
  }
}