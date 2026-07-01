<?php

namespace App\Controller;

use App\Services\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use function PHPSTORM_META\type;

final class InvoiceController extends AbstractController
{
    /**
     * Create
     */
    #[Route('/customerrole/invoice/create', name: 'invoice.create' , methods:['GET'])]
    public function create(Request $request, InvoiceService $invoiceService)
    {
        return $this->render('invoice/create.html.twig',[
            "articles"  => $invoiceService->create($this->getUser())["articles"],
            "addresses" => $invoiceService->create($this->getUser())["addresses"],
            "data"      =>$request->get('data')
        ]);
    }
    /**
     * Save & 'Do Paymen per Paypal'
     */
    #[Route('cutomerrole/invoice/paymentpaypal', name: 'invoice.paymentPaypal', methods:['POST'])]
    public function paypentPaypal(Request $request, InvoiceService $invoiceService)
    {
        //Get the data from invoiceService
        $data=$invoiceService->paymentPaypal($this->getUser(), $request->get('addressId'));
        if($data['success'] != true)
            {
                return $this->redirectToRoute("invoice.create",[
                    "data"=>$data,
                ]);
            }
        //Get the Link To Paypal
        $linkToPaypal=$data['link'];
        return $this->redirect($linkToPaypal);
    }
    /**
     * Success Payment per Paypal
     */
    #[Route('/invoice/paypalSuccess', name: 'invoice.paypalSuccess', methods: ['GET'])]
    public function paypalSuccess(InvoiceService $invoiceService,Request $request)
    {
        //Paypal Success Proces
        $data=$invoiceService->paypalSuccess($request->get('token'), $this->getUser());
        return $this->redirectToRoute('invoice.show',[
            'data'=>$data,
        ]);
    }
    /**
     * Error Payment per Paypal
     */
    #[Route('/invoice/paypalCancel', name: 'invoice.paypalCancel', methods: ['GET'])]
    public function paypalCancel(InvoiceService $invoiceService, Request $request)
    {
        return $this->redirectToRoute('invoice.show',[
            'data'=>$invoiceService->paypalCancel()
        ]);
    }
    /**
     * Show 'Get last Invoice'
     */
    #[Route('/customerrole/invoice/show', name: 'invoice.show', methods:['GET'])]
    public function show(InvoiceService $invoiceService, Request $request)
    {
        //dd($invoiceService->show($this->getUser()));
        return $this->render('invoice/show.html.twig',[
            'data'          =>$request->get('data'),
            'lastInvoice' =>$invoiceService->show($this->getUser())

        ]);
    }

    /**
     * Get Invoices from Today & Yesterday 'NOT CLOSED'
     * Response of Ajax-Request
     */
    #[Route('/cookrole/invoice/current', name: 'invoice.current', methods: ['POST'])]
    public function current(InvoiceService $invoiceService)
    {
       return $this->json([
        'invoices'=>$invoiceService->current()
       ]); 
    }
    /**
     * Set the Invoice closed =>'The Invoice is allredy to send to the Custumer'
     * Response of Ajax-Request
     */
    #[Route('/cookrole/invoice/closeInvoice', name: 'invoice.closeInvoice', methods: ['POST'])]
    public function closeInvoice(InvoiceService $invoiceService, Request $request)
    {
       return $this->json([
         'invoices'=>$invoiceService->closeInvoice($request->get('invoiceId'))
        ]);
    }
    /**
     * Get Invoices from Today & Yesterday 'CLOSED'
     * Response of Ajax-Request
     */
    #[Route('/cookrole/invoice/finish', name: 'invoice.finish', methods: ['POST'])]
    public function finish(InvoiceService $invoiceService)
    {
        return $this->json([
            'invoices'=>$invoiceService->finish()
        ]);
    }
    /**
     * Set the Invoice offen =>'The Invoice wll be prepeared agin'
     * Response of Ajax-Request
     */
    #[Route('/cookrole/invoice/offenInvoice', name: 'invoice.offenInvoice', methods: ['POST'])]
    public function offenInvoice(InvoiceService $invoiceService, Request $request)
    {
        return $this->json([
         'invoices'=>$invoiceService->offenInvoice($request->get('invoiceId'))
        ]);
    }


}
