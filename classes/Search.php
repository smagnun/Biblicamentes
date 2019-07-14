<?php

class Search {

  //public $termsToSearch;

  // public function __construct($termsToSearch) {

  //   $this->termsToSearch = $termsToSearch;
  //   return $this->termsSerched = $this->searchTerms($this->termsToSearch);

  // }

	public static function searchTerms($termsToSearch, $page = 1, $totalRowsPerPage) {
    if($page > 1):
      $startRow = ($page - 1) * $totalRowsPerPage;
    else:
      $startRow = 0;
    endif;

    $query = "SELECT livro_nome,
                      cap, 
                      ver, 
                      texto,
                      livro_slug,
                      MATCH (texto) AGAINST (:terms) 
                      AS SCORE 
    FROM bibliakja AS biblia
    LEFT JOIN biblia_livros AS bl
    ON bl.cod_livro = biblia.cod_livro
    WHERE MATCH (texto) AGAINST (:terms) > 0
    LIMIT $startRow,$totalRowsPerPage";

		$connection = Connect::getConnection();
		$statement = $connection->prepare($query);

		$statement->bindValue(':terms', $termsToSearch);
		$statement->execute();
		return $statement->fetchAll();
  }
  
  
  public static function searchTermsByBook($termsToSearch, $book, $startRow = 0, $totalRowsPerPage) { 
    //$book = livro_slug

    $query = "SELECT livro_nome,
                      cap, 
                      ver, 
                      texto,
                      livro_slug,
                      MATCH (texto) AGAINST (:terms) 
                      AS SCORE 
    FROM bibliakja AS biblia
    LEFT JOIN biblia_livros AS bl
    ON bl.cod_livro = biblia.cod_livro
    WHERE MATCH (texto) AGAINST (:terms) > 0
    AND livro_slug = :book
    LIMIT $startRow,$totalRowsPerPage";

		$connection = Connect::getConnection();
		$statement = $connection->prepare($query);

		$statement->bindValue(':terms', $termsToSearch);
		$statement->bindValue(':book', $book);
		$statement->execute();
		return $statement->fetchAll();
  }
  

  public static function totalRowsByBooks($termsToSearch) {

    $query = "SELECT  livro_nome, 
                      COUNT(*) AS TOTAL
    FROM bibliakja AS biblia
    LEFT JOIN biblia_livros AS bl
    ON bl.cod_livro = biblia.cod_livro
    WHERE MATCH (texto) AGAINST (:terms) > 0
    GROUP BY bl.id";

    $connection = Connect::getConnection();
    $statement = $connection->prepare($query);

    $statement->bindValue(':terms', $termsToSearch);
    $statement->execute();
    return $statement->fetchAll();
  }


  public static function totalRows($termsToSearch) {

    $query = "SELECT COUNT(cod_livro) AS TOTAL
    FROM bibliakja
    WHERE MATCH (texto) AGAINST (:terms) > 0";

    $connection = Connect::getConnection();
    $statement = $connection->prepare($query);

    $statement->bindValue(':terms', $termsToSearch);
    $statement->execute();
    return $statement->fetch();
  }


  public function paginationLinks($totalPages, $pagesStart, $pagesMid, $pagesEnd, $pageSelected, $terms) {
    $allLinksTogether = "";
    $linkCreated = "";

    // CONSTRUCT PAGINATION AND 
    // HIGHLIGHT THE SELECTED ONE
    foreach ($pagesStart as $pages):
      $linkCreated = $this->paginationLinksCreator($terms, $pages, $pageSelected);

      $allLinksTogether = $allLinksTogether . $linkCreated;

      //$allLinksTogether = $allLinksTogether . "<a href='/pesquisa.php?termos=$terms&pagina=$pages' class='pagination--number'>$pages</a>";
    endforeach;

    if (!empty($pagesMid)):
      $allLinksTogether = $allLinksTogether . " ... ";

      foreach ($pagesMid as $pages):
        $linkCreated = $this->paginationLinksCreator($terms, $pages, $pageSelected);

        $allLinksTogether = $allLinksTogether . $linkCreated;

        //$allLinksTogether = $allLinksTogether . "<a href='/pesquisa.php?termos=$terms&pagina=$pages' class='pagination--number'>$pages</a>";
      endforeach;

      //echo implode(" ", $pagesStart) . " ... " . implode(" ", $pagesMid) . " ... " . implode(" ", $pagesEnd);
    endif;
    
    if (!empty($pagesEnd)):
      $allLinksTogether = $allLinksTogether . " ... ";

      foreach ($pagesEnd as $pages):
        $linkCreated = $this->paginationLinksCreator($terms, $pages, $pageSelected);

        $allLinksTogether = $allLinksTogether . $linkCreated;
        
        //$allLinksTogether = $allLinksTogether . "<a href='/pesquisa.php?termos=$terms&pagina=$pages' class='pagination--number'>$pages</a>";
      endforeach;
      //echo implode(" ", $pagesStart) . " ... " . implode(" ", $pagesEnd);
    endif;
    
    //echo "<a href='/pesquisa.php?termos=$terms&pagina=$i' class='pagination--number'>$i</a>";


    return $allLinksTogether;
  }

  private function paginationLinksCreator($terms, $pages, $pageSelected) {
    // if ($pageSelected != $pages):
    //   $linkCreated = paginationLinksCreator($terms, $pages);
    // else:
    //   $linkCreated = paginationLinksCreator($terms, $pages, true);
    // endif;

    return ($pageSelected != $pages) ? 
      // common link
      "<a href='/pesquisa.php?termos=$terms&pagina=$pages' class='pagination--number'>$pages</a>": 
      // selected link
      "<a href='/pesquisa.php?termos=$terms&pagina=$pages' class='pagination--number pagination--number__selected'>$pages</a>";
  }
}

?>