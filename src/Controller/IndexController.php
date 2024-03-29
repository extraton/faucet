<?php namespace App\Controller;

use App\Entity;
use App\Repository\RequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use TON\Abi\ParamsOfEncodeMessage;
use TON\Client\ClientConfig;
use TON\Processing\ParamsOfSendMessage;
use TON\Processing\ParamsOfWaitForTransaction;
use TON\TonClient;

class IndexController extends AbstractController
{
    private const TOKEN_AMOUNT = '111001000000';

    public function __construct(
        private string $siteName,
        private string $domain,
        private string $recaptchaSiteKey,
        private string $recaptchaSecretKey,
        private string $walletAddress,
        private string $walletPublic,
        private string $walletSecret,
        private string $walletColdAddress,
        private EntityManagerInterface $em,
    )
    {
    }

    public function index(Request $request): Response
    {
        $jsConfig = [
            'siteName' => $this->siteName,
            'domain' => $this->domain,
            'recaptchaSiteKey' => $this->recaptchaSiteKey,
            'walletAddress' => $this->walletAddress,
            'walletColdAddress' => $this->walletColdAddress,
            'ip' => $request->getClientIp(),
        ];
        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE)),
            'frontDevPort' => '8100',
            'styles' => [
                'https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900',
                'https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css',
            ],
            'scripts' => [],
        ];

        return $this->render('app.html.twig', $data);
    }

    public function request(Request $request): Response
    {
        $form = $request->request->get('form');
        $address = $form['address'] ?? null;
        $captcha = $form['captcha'] ?? null;
        $ip = $request->getClientIp();

        try {
            $this->checkCaptcha($captcha, $request->getClientIp());
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException('Wrong captcha.');
        }

        /** @var RequestRepository $requestRepository */
        $requestRepository = $this->em->getRepository(Entity\Request::class);
        $recentlyRequest = $requestRepository->findRecently($address, $ip);
        if (null !== $recentlyRequest) {
            throw new TooManyRequestsHttpException('Sorry, you recently received tokens.');
        }

        $this->send($address);

        $currentRequest = new Entity\Request($address, $ip);
        $this->em->persist($currentRequest);
        $this->em->flush();

        return new JsonResponse([]);
    }

    private function send(string $address): void
    {
        $abi = ['type' => 'Json', 'value' => file_get_contents(__DIR__ . '/../../contracts/SafeMultisigWallet.abi.json')];
        $client = new TonClient(new ClientConfig(['network' => ['endpoints' => ['net1.ton.dev', 'net5.ton.dev']]]));
        $message = $client->abi()->async()->encodeMessageAsync(new ParamsOfEncodeMessage(
            [
                'abi' => $abi,
                'address' => $this->walletAddress,
                'call_set' => ['function_name' => 'sendTransaction', 'input' => ['dest' => $address, 'value' => self::TOKEN_AMOUNT, 'bounce' => false, 'flags' => 0, 'payload' => '']],
                'signer' => ['type' => 'Keys', 'keys' => ['public' => $this->walletPublic, 'secret' => $this->walletSecret]],
            ],
        ))->await()->getMessage();
        $shardBlockId = $client->processing()->async()->sendMessageAsync(new ParamsOfSendMessage([
            'message' => $message,
            'abi' => $abi,
            'send_events' => false,
        ]))->await()->getShardBlockId();
        //@TODO move 'waitForTransaction' to front
        $client->processing()->async()->waitForTransactionAsync(new ParamsOfWaitForTransaction([
            'message' => $message,
            'abi' => $abi,
            'shard_block_id' => $shardBlockId,
            'send_events' => false,
        ]))->await();
    }

    /** @throws \Exception */
    private function checkCaptcha(string $captcha, string $ip): void
    {
        $recaptcha = new ReCaptcha($this->recaptchaSecretKey);
        $response = $recaptcha->setExpectedHostname($this->domain)->verify($captcha, $ip);
        if (!$response->isSuccess()) {
            throw new \Exception('Recaptcha error codes: ' . json_encode($response->getErrorCodes()));
        }
    }
}
