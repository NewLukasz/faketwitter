<?php

namespace App\Controller;

use App\Form\ProfileImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UserProfileType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class SettingsProfileController extends AbstractController
{
    #[Route('/settings/profile', name: 'app_settings_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var $user User */
        $user = $this->getUser();

        $userProfile = $user->getUserProfile() ?? new UserProfile();

        $form = $this->createForm(UserProfileType::class, $userProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile = $form->getData();
            $user->setUserProfile($userProfile);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "Your user setting were saved"
            );

            return $this->redirectToRoute(
                'app_settings_profile'
            );
        }


        return $this->render('settings_profile/profile.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/settings/profile-image', name: 'app_settings_profile_image')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profileImage(
        Request                $request,
        SluggerInterface       $slugger,
        EntityManagerInterface $entityManager
    ): Response
    {
        $form = $this->createForm(ProfileImageType::class);

        /** @var User $user */
        $user = $this->getUser();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profileImageFile = $form->get('profileImage')->getData();
            if ($profileImageFile) {
                $originalFileName = pathinfo(
                    $profileImageFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename . '-' . uniqid() . '.' . $profileImageFile->guessExtension();

                try {
                    $profileImageFile->move(
                        $this->getParameter('profile_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                    //handle errors here
                }

                $profile = $user->getUserProfile() ?? new UserProfile();
                $profile->setImage($newFileName);
                $user->setUserProfile($profile);
                //wrong practise to have all path to file easier to move files to another places
                $entityManager->persist($profile);
                $entityManager->flush();
                $this->addFlash('success', 'Your profile image is updated');

                return $this->redirectToRoute('app_settings_profile_image');
            }
        }

        return $this->render('settings_profile/profile_image.html.twig', [
            'form' => $form
        ]);
    }
}
