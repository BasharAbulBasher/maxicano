<?php
namespace App\Services;

use App\Entity\Address;
use App\Entity\Article;
use App\Entity\Invoice;
use App\Entity\Order;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InvoiceService
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private HttpClientInterface $cleint;
    private Pusher $pusher;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, HttpClientInterface $cleint, Pusher $pusher)
    {
        $this->entityManager=$entityManager;
        $this->requestStack=$requestStack;
        $this->cleint=$cleint;
        $this->pusher=$pusher;
    }
    /**
     * Create
     */
    public function create(User $user)
    {
        //Get Session
        $session=$this->requestStack->getSession();
        //Get the Cart from Session
        $cart=$session->get('cart',[]);
        $articles_arr=[];
        foreach($cart as $articleId)
            {
                //Get Article by Id
                $article=$this->entityManager->getRepository(Article::class)->find($articleId);
                //Push the Article into Array
                array_push($articles_arr, $article->toArray());
            }
        //Get the addresses
        $addresses=$this->entityManager->getRepository(Address::class)->findBy(["user"=>$user]);
        return [
            "articles"=>$articles_arr,
            "addresses"=>$addresses,
            ];
    }
    /**
     * Payment Per Paypal
     */
    public function paymentPaypal(User $user, $addressId)
    {
        //Validation
        $errors=$this->validation($user, $addressId);
        if(!empty($errors))
            {
                return [
                    'success'   =>false,
                    'message'   =>'Validation Errors',
                    'errors'    =>$errors
                ];
            }
        //Get Session
        $session= $this->requestStack->getSession();
        //Set the Address Id in Session
        $session->set('addressId',$addressId);
        //Get the Cart from Session
        $cart=$session->get('cart',[]);
        //Claculate the Total Price in Cart
       $totalPrice=0;
        foreach($cart as $articleId)
            {
                $article=$this->entityManager->getRepository(Article::class)->find($articleId);
                $totalPrice+=$article->getPrice();
            }
        //Get the Response from 'Paypal-Sandbox'
        $response = $this->cleint->request(
            'POST',
            'https://api-m.sandbox.paypal.com/v1/oauth2/token',
            [
                'auth_basic' => [
                    $_ENV['PAYPAL_CLIENT_ID'],
                    $_ENV['PAYPAL_SECRET']
                ],
                'body' => [
                    'grant_type' => 'client_credentials'
                ]
            ]
        );
        //Set the Response in Array
        $response_arr=$response->toArray();
        //Get the Access_Token
        $accessToken = $response_arr['access_token'];
        //Action of Paypal-Protzess
         $orderResponse = $this->cleint->request(
        'POST',
        'https://api-m.sandbox.paypal.com/v2/checkout/orders',
        [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => $totalPrice,
                        ],
                    ],
                ],
                'application_context' => [
                    'return_url' => 'http://127.0.0.1:8000/invoice/paypalSuccess',
                    'cancel_url' => 'http://127.0.0.1:8000/invoice/paypalCancel',
                ],
            ],
        ]
        );
        $order=$orderResponse->toArray();
        foreach ($order['links'] as $link) {
            if ($link['rel'] === 'approve')
            {
                return[
                    'success'   =>true,
                    'link'      =>$link['href']
                    ];
            }
        }

    }
    /**
     * Paypal Success
     */
    public function paypalSuccess($token, User $user)
    {
        //Get the Session
        $session=$this->requestStack->getSession();
        //Get Address Id from Session
        $addressId=$session->get('addressId');
        //Find Address By Id
        $address=$this->entityManager->getRepository(Address::class)->find($addressId);
        //Get Cart from Session
        $cart=$session->get('cart',[]);
        //Injection new Invoice
        $invoice=new Invoice();
        //Setter
        $invoice->setAddress($address);
        $invoice->setDate(new DateTime('now'));
        $invoice->setToken($token);
        $invoice->setClosed(false);
        //Save the Invoice
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        //Loop Cart
        foreach($cart as $articleId)
            {
                //Find Article
                $article=$this->entityManager->getRepository(Article::class)->find($articleId);
                //Injection new Order
                $order=new Order();
                $order->setArticle($article);
                $order->setInovice($invoice);
                //Save the Order
                $this->entityManager->persist($order);
                $this->entityManager->flush();
            }
        $this->sendToPusher();
        return[
            'success'=>true,
            'message'=>'Wir liefern ihnen innerhlab 15min deine Bestellung',
        ];
    }
    /**
     * Show 'Get Last Invoice'
     */
    public function show(User $user)
    {
        //Get Last InvoiceId
        $lastInvoiceId=$this->entityManager->getRepository(Invoice::class)->getLastInvoiceId($user);
        //Get last Invoice
        $invoice = $this->entityManager->getRepository(Invoice::class)->find($lastInvoiceId['invoiceId']);
        return $this->getInvoice($invoice);
    }
    /**
     * Payment Error
     */
    public function paypalCancel()
    {
        return [
            'success'=>false,
            'message'=>'Ihre Betzahlung konte LEIDER NICHT erfolgrich abgeschlossen werden',
        ];
    }
    /**
     * Validation Payment per Paypal
     */
    private function validation(User $user, $addressId)
    {
        $errors=[];
        //Address Id is Empty
        if(empty($addressId))
            {
                $errors["addressId"]="Bitte fügen Sie Ihre Addresslieferung";
            }
        //Get Address by User
        $addressByUser=$this->entityManager->getRepository(Address::class)->findOneBy(['user'=>$user]);
        //Error by logedin user
        if($user != $addressByUser->getUser())
            {
                $errors["user"]="Unerwartet Fehler, Versuchen Sie später zu betzahlen";
            }
        return $errors;
    }
    /**
     * Get Today and Yesterday Invoices, they are not closed yet
     */
    public function current()
    {
        //Get TodayYesterday-Invoices, they are NOT CLOSED
        $todayInvoices = $this->todayInvoices(false);
        return $todayInvoices;
    }
    /**
     * Set Invoice in closed => 'true'
     */
    public function closeInvoice($invoiceId)
    {
        //Get the Invoice
        $invoice=$this->entityManager->getRepository(Invoice::class)->find($invoiceId);
        $invoice->setClosed(true);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        return $this->todayInvoices(false);
    }
    /**
     * Get Today and Yesterday Invoices, they are closed
     */
    public function finish()
    {
        //Get TodayYesterday-Invoices, they are CLOSED
        $finish_todayInvoices = $this->todayInvoices(true);
        return $finish_todayInvoices;
    }
    /**
     * Set Invoice in offen => 'false'
     */
    public function offenInvoice($invoiceId)
    {
        //Get the Invoice
        $invoice=$this->entityManager->getRepository(Invoice::class)->find($invoiceId);
        $invoice->setClosed(false);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        return $this->todayInvoices(true);
    }

    /**
     *'Today-Invoices' Get All Invoices from Today & Yersterday
     *
     */
    private function todayInvoices($closed)
    {
        $invoices=$this->entityManager->getRepository(Invoice::class)->today_yesterday_invoices($closed);
        $invoices_arr=[];
        foreach($invoices as $invoice)
            {
                array_push($invoices_arr, $this->getInvoice($invoice));
            }
        return $invoices_arr;
    }
    /**
     * Find & Generate the Invoice
     */
    private function getInvoice(Invoice $invoice)
    {
        //Get Address By Invoice
        $address=$invoice->getAddress();
        //Get Orders by Invoice
        $orders=$invoice->getOrders();
        $orders_arr=[];
        //Loop Orders
        foreach($orders as $order)
            {
                array_push($orders_arr, $order->toArray());
            }
        return[
            'invoiceId' => $invoice->getId(),
            'closed'    => $invoice->isClosed(),
            'date'      => $invoice->getDate()->format('d.m.Y'),
            'firstName' => $address->getFirstName(),
            'lastName'  => $address->getLastName(),
            'street'    => $address->getStreet(),
            'hausNr'    => $address->getHausNr(),
            'plz'       => $address->getPlz(),
            'city'      => $address->getCity(),
            'orders'    => $orders_arr,
        ];
    } 

    /**
     * Send today Invoices to Pusher
     */
    private function sendToPusher()
    {
        $invoices=$this->todayInvoices(false);
        //Send to Pusher
        $this->pusher->trigger('cook', 'event', [
            'invoices' => $invoices,
        ]);

    }

}
