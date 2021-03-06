<?php 
	require_once 'classes/load-classes.php';

	//https://www.w3schools.com/pHP/php_exception.asp
	//https://www.php.net/manual/en/language.exceptions.php
	//https://www.php.net/manual/en/internals2.opcodes.catch.php
 	try {
		$bibliaVersao = $_GET['biblia_versao'];
		$livroSlugCurr = $_GET['livro_slug'];

		if (isset($_GET['livro_slug']) && !empty($_GET['livro_slug']) && isset($_GET['cap']) && !empty($_GET['cap'])):

			$cap = $_GET['cap'];
			$verses = Study::getChapterContent($bibliaVersao, $livroSlugCurr, $cap);
			$chapter = Study::getChapter($livroSlugCurr, $cap);
			$copy = Study::getCopyright($bibliaVersao);

		//elseif (isset($_GET['livro_slug']) && !empty($_GET['livro_slug']) && !isset($_GET['cap'])):

			//$cap = 1;

		endif;

		$book = Study::getBook($livroSlugCurr);

		if ($book['id'] > 0 && $book['id'] <= 66):
			$previousAndNext = Study::getPreviousAndNextBooks($book['id']); //[0] previous book, [1] next book, [2] current book
		endif;

		$books = Study::getBooksAll(); //show all

 	} catch (Exception $e) {
 	 	echo ('MENSAGEM: ' . $e->getMessage());
 	}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
	<meta charset="utf-8">
	<title><?= $book['livro_nome'] ?> * Biblicamentes</title>
	<link rel="stylesheet preload" href="/css/styles--estudo.css" as="style">
	
<?php 
	require_once 'html__head.php';
	require_once 'body__header.php'; 
?>

<!-- BEGIN CONTENT  -->
	<section class="content content--row l-container">

		<main class="content__main">

		<?php if (!empty($book)):

				//call capitulos.php if both $_GET is set
				//call livros.php if only livro_slug is set
				if (isset($_GET['livro_slug']) && isset($_GET['cap'])):

					require_once 'kja/capitulos.php';

				elseif (isset($_GET['livro_slug']) && !isset($_GET['cap'])):

					require_once 'kja/livros-intro.php';

				endif;
			
			else:	
			?>

			<div class="content__error">
				Livro Inexistente. Use o menu e navegue entre os livros.
			</div>

			<?php endif; //!empty $book ?>

		</main>

		<?php if (!empty($book)):	?>

		<aside class="aside-nav js-aside-modal">
			
			<button type="button" class="aside-nav__button js-aside-button" aria-label="Navegar nos Capítulos e Livros">Capítulos</button>

			<div class="aside-nav__bible">
				<div class="aside-nav__bible--chapters">
					
					<!-- CURRENT BOOK -->
					<h3 class="aside-nav__bible--intro"><a class="bible__main-btn" href="/kja/<?= $previousAndNext[2]['livro_slug'] ?>">Introdução <?= $previousAndNext[2]['livro_nome'] ?></a></h3>

					<!-- BOOK CHAPTERS -->
					<?php for ($i = 1; $i < $book['total_capitulos'] + 1; $i++): 
					?>
						
						<a class="bible__main-btn" href="/kja/<?= $book['livro_slug'] ?>/<?= $i ?>"><?= $i ?></a>
						
					<?php endfor; ?>

				</div>

				<div class="aside-nav__bible--books">

					<!-- PREVIOUS BOOK -->
					<a class="bible__main-btn" href="/kja/<?= $previousAndNext[0]['livro_slug'] ?>"><i class="fas fa-chevron-left"></i> Intro <?= $previousAndNext[0]['livro_nome'] ?></a>

					<!-- NEXT BOOK -->
					<a class="bible__main-btn" href="/kja/<?= $previousAndNext[1]['livro_slug'] ?>">Intro <?= $previousAndNext[1]['livro_nome'] ?> <i class="fas fa-chevron-right"></i></a>
				</div>
			</div>
		</aside>
	</section> 


	<?php // PREVIOUS & NEXT CHAPTERS
		$totalCapsCurr = $book['total_capitulos'];
		
		if ((isset($livroSlugCurr) && !isset($cap)) || ($cap > 0 && $cap <= $totalCapsCurr)):
		
			$totalCapsPrev = $previousAndNext[0]['total_capitulos'];
			$totalCapsNext = $previousAndNext[1]['total_capitulos'];
			$totalCapsCurr = $previousAndNext[2]['total_capitulos'];
			$livroSlugPrev = $previousAndNext[0]['livro_slug'];
			$livroSlugNext = $previousAndNext[1]['livro_slug'];
			$livroNomePrev = $previousAndNext[0]['livro_nome'];
			$livroNomeNext = $previousAndNext[1]['livro_nome'];
			$livroNomeCurr = $previousAndNext[2]['livro_nome'];

			if (isset($cap)):
				//internal caps of the current book
				if ($cap > 1 && $cap < $totalCapsCurr):
					$prevBookName = $livroNomeCurr;
					$nextBookName = $livroNomeCurr;
					$prevBook = $livroSlugCurr;
					$nextBook = $livroSlugCurr;
					$prevCap = $cap - 1;
					$nextCap = $cap + 1;
				// books with only 1 cap, like judas
				elseif ($cap == 1 && $totalCapsCurr == 1): 
					$prevBookName = $livroNomeCurr;
					$nextBookName = $livroNomeNext;
					$prevBook = $livroSlugCurr;
					$nextBook = $livroSlugNext;
					$prevCap = "";
					$nextCap = "";
				// first cap
				elseif ($cap == 1): 
					$prevBookName = $livroNomeCurr;
					$nextBookName = $livroNomeCurr;
					$prevBook = $livroSlugCurr;
					$nextBook = $livroSlugCurr;
					$prevCap = "";
					$nextCap = $cap + 1;
				// last cap
				elseif ($cap == $totalCapsCurr):	
					$prevBookName = $livroNomeCurr;
					$nextBookName = $livroNomeNext;				
					$prevBook = $livroSlugCurr;
					$nextBook = $livroSlugNext;
					$prevCap = $cap - 1;
					$nextCap = "";
				endif;
			else:
				// book intro
				$prevBookName = $livroNomePrev;
				$nextBookName = $livroNomeCurr;
				$prevBook = $livroSlugPrev;
				$nextBook = $livroSlugCurr;
				$prevCap = $totalCapsPrev;
				$nextCap = 1;
			endif; //isset($cap)
	?>

	
		<a class="chapter-nav chapter-nav--left js-chapter-nav--left has-tooltip--toright" aria-label="<?php echo $prevBookName . " " . ($prevCap == "" ? "intro" : $prevCap); ?>" href="/kja/<?= $prevBook ?>/<?= $prevCap ?>">
			<i class="fas fa-angle-left fa-2x"></i>
		</a>

		<a class="chapter-nav chapter-nav--right js-chapter-nav--right has-tooltip--toleft" aria-label="<?php echo $nextBookName . " " . ($nextCap == "" ? "intro" : $nextCap); ?>" href="/kja/<?= $nextBook ?>/<?= $nextCap ?>">
			<i class="fas fa-angle-right fa-2x"></i>
		</a>


	<?php 
			endif; //previous and next buttons 
		
		endif; //!empty $book	
	?>

	<!-- END CONTENT  -->

<?php require_once 'body__footer.php' ?>