<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use LLPhant\Embeddings\DataReader\FileDataReader;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;

#[AsCommand(
    name: 'GenerateEmbeddings',
    description: 'Genere les embeddings',
)]
class GenerateEmbeddingsCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Hello ! Nous allons générer les embeddings de vos données.");

        $io->section("Lecture des données");
        $dataReader = new FileDataReader(__DIR__ . '/../../public/best_practices.rst');
        $documents = $dataReader->getDocuments();
        $io->success("Les données ont été lues avec succès, et ".count($documents)." documents ont été trouvés.");
        
        $io->section("Découpage des documents");
        $splittedDocuments = DocumentSplitter::splitDocuments($documents, 500);
        $io->success("Les documents ont été découpés avec succès en ".count($splittedDocuments)." documents de 500 mots maximum.");
        
        $io->section("Génération des embeddings");
        $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator();
        $embeddedDocuments = $embeddingGenerator->embedDocuments($splittedDocuments);
        $io->success("Les embeddings ont été générés avec succès.");

        $io->section("Sauvegarde des embeddings");
        $vectorStore = new FileSystemVectorStore();
        $vectorStore->addDocuments($embeddedDocuments);
        $io->success("Les embeddings ont été sauvegardés avec succès.");
        
        $io->success("Les embeddings ont été générés avec succès et stockés dans le fichier documents-vectorStore.json");

        return Command::SUCCESS;
    }
}
