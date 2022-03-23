<?php
	
	namespace App\EventSubscriber;
	
	use App\Entity\User;
	use Symfony\Component\EventDispatcher\EventSubscriberInterface;
	use Symfony\Component\Security\Core\Exception\AuthenticationException;
	use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
	use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
	use Symfony\Component\Security\Http\Event\CheckPassportEvent;
	
	class CheckVerifiedUserSubscriber implements EventSubscriberInterface
	{
		
		public static function getSubscribedEvents()
		{
			return [
				CheckPassportEvent::class => ['onCheckPassword', -10]
			];
		}
		
		public function onCheckPassword(CheckPassportEvent $event)
		{
			$passport = $event->getPassport();
			if (!$passport instanceof UserPassportInterface) {
				throw new \Exception('Unexpected passport type');
			}
			
			$user = $passport->getUser();
			if (!$user instanceof User) {
				throw new \Exception('Unexpected user type');
			}
			
			if (!$user->getIsVerified()) {
				throw new CustomUserMessageAuthenticationException(
					'Please verify your account before loggin in.'
				);
			}
		}
	}