<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\Email;
use AppBundle\Entity\Person;
use AppBundle\Entity\Phone;
use AppBundle\Form\AddressType;
use AppBundle\Form\EmailType;
use AppBundle\Form\PersonType;
use AppBundle\Form\PhoneType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PersonController extends Controller
{
    /**
     * @Route("/newPerson", name="create_person")
     */
    public function newPersonAction(Request $request)
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $person = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
//            return new Response('New person is add in DB!');
            return $this->redirect('/' . $person->getId());
        }
        return $this->render('@App/Person/new_person.html.twig', ['form_person'=>$form->createView()]);

    }

    /**
     * @Route("/{id}/modifyPerson", name="modify")
     */
    public function modifyPersonAction(Request $request, Person $person)
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $person = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
//            return new Response("Person is successfully modified");
            return $this->redirect('/');
        }

        return $this->render('@App/Person/modify_person.html.twig', [
            'form_person' => $form->createView(),
            'person' => $person
        ]);
    }

    /**
     * @Route("{id}/deletePerson")
     */
    public function deletePersonAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Person');
        $personToRemove = $repo->find($id);
        if(!$personToRemove){
            return new Response('Person with id: ' . $id . ' not exist in database');
        }
        $em->remove($personToRemove);
        $em->flush();

         return new Response('Person with id: '. $id . ' is deleted');
    }

    /**
     * @Route("{id}")
     */
    public function showPersonById($id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Person');
        $person = $repo->find($id);
        //here i show address for person
        $addressRepo = $em->getRepository('AppBundle:Address');
        $addresses = $addressRepo->findBy(array('person' => $person));

        $addressToShow = '<ul>';
        foreach ($addresses as $address){
            $city = 'City: ' .$address->getCity() . '<br>';
            $street = 'Street: ' .$address->getStreet() . '<br>';
            $house = 'House: ' .$address->getHouse() .'<br>';
            $addressToShow .= '<li>'. $city .' - ' . $street .' - ' . $house .'</li>';
        }
        $addressToShow .= '</ul>';

        //here i show email for person
        $emailRepo = $em->getRepository('AppBundle:Email');
        $emails = $emailRepo->findBy(array('person' => $person));

        $emailToShow = '<ul>';
        foreach($emails as $email){
            $data = $email->getEmailAddress() . '<br>';
            $emailType = 'Type: ' .$email->getType() . '<br>';
            $emailToShow .='<li>' . $emailType. $data .'</li>';
        }
        $emailToShow .= '</ul>';

        //here i show phone number for person
        $phoneRepo = $em->getRepository('AppBundle:Phone');
        $phones = $phoneRepo->findBy(array('person'=> $person));

        $phoneToShow = '<ul>';
        foreach($phones as $phone){
            $number = $phone->getPhoneNumber() . '<br>';
            $phoneType = 'Type: '. $phone->getType() . '<br>';
            $phoneToShow .='<li>' . $phoneType . $number .'</li>';
        }
        $phoneToShow .= '</ul>';

       return new Response('
       <ul>
            Be careful, if you click here you will delete this person !!<a href="/'.$person->getId().'/deletePerson">Delete</a>!!
            <li>Id: ' . $person->getId() .'</li>
            <li>First Name: '. $person->getFirstName() .'</li>
            <li>Last Name: '. $person->getLastName() .'</li>
            <li>Description: '. $person->getDescription() .'</li>
           <li>Address: '. $addressToShow . '</li><a href="/'.$person->getId().'/addAddress">Add new Address</a>
           <li>Email: ' .$emailToShow. '</li><a href="/'.$person->getId().'/addEmail">Add new Email</a>
           <li>Phone: ' . $phoneToShow.'</li><a href="/' .$person->getId() .'/addPhone">Add new Phone</a>
       </ul>
      
       <a href="/">Click here if you want to see all persons</a>');
    }
    /**
     * @Route("/")
     */
    public function showAllPersons(){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Person');
        $persoane = $repo->findAll();

        $html = '<ul>';
        foreach ($persoane as $person) {
            $html .= '<li><a href="/'.$person->getId().'">'.$person->getFirstName(). ' ' .$person->getLastName().'</a></li>';
        }
        $html .= '</ul>';
        $addNewPerson = '<br><a href="/newPerson">Create new Person</a>';
        return new Response($html . $addNewPerson);
    }

    /**
     * @Route("{id}/addAddress", name="addAddress")
     */

    public function addNewAddress(Request $request, $id){

        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('AppBundle:Person')->find($id);

        if($form->isSubmitted() && $form->isValid()){
            $address = $form->getData();
            $address->setPerson($person);
            $em->persist($address);
            $em->flush();
//            return new Response("Address is successfully added");
            return $this->redirect('/'.$id);
        }
        return $this->render('@App/Person/add_address.html.twig', [
            'form_address' => $form->createView(),
            'person' => $person
        ]);
    }

    /**
     * @Route("{id}/addEmail", name="addEmail")
     */
    public function addNewEmail(Request $request, $id){

        $email = new Email();
        $form = $this->createForm(EmailType::class, $email);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('AppBundle:Person')->find($id);

        if($form->isSubmitted() && $form->isValid()){
            $email = $form->getData();
            $email->setPerson($person);
            $em->persist($email);
            $em->flush();
//            return new Response('Email is successfully added');
            return $this->redirect('/'.$id);
        }
        return $this->render('@App/Person/add_email.html.twig', [
            'form_email' => $form->createView(),
            'person' => $person
        ]);
    }
    /**
     * @Route("{id}/addPhone", name="addPhone")
     */
    public function addNewPhone(Request $request, $id){

        $phone = new Phone();
        $form = $this->createForm(PhoneType::class, $phone);
        $form->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('AppBundle:Person')->find($id);

        if($form->isSubmitted() && $form->isValid()){
            $phone = $form->getData();
            $phone->setPerson($person);
            $em->persist($phone);
            $em->flush();
//            return new Response('Email is successfully added');
            return $this->redirect('/'.$id);
        }
        return $this->render('@App/Person/add_phone.html.twig', [
            'form_phone' => $form->createView(),
            'person' => $person
        ]);
    }


}
