<?php

namespace App\Controller;

use App\Form\ChatbotType;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatbotController extends AbstractController
{
    #[Route('/', name: 'app_chatbot')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ChatbotType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData()['question'];

            $vectorStore = new FileSystemVectorStore('../documents-vectorStore.json');
            $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator();

            $qa = new QuestionAnswering(
                $vectorStore,
                $embeddingGenerator,
                new OpenAIChat()
            );

            $answer = $qa->answerQuestion($question);
        }

        return $this->render('chatbot/index.html.twig', [
            'form' => $form,
        ]);
    }
}
