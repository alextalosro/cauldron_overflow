<?php
	
	namespace App\EventSubscriber;
	
	use App\Entity\User;
	use App\Security\AccountNotVerifiedAuthenticationException;
	use Symfony\Component\EventDispatcher\EventSubscriberInterface;
	use Symfony\Component\HttpFoundation\RedirectResponse;
	use Symfony\Component\Routing\RouterInterface;
	use Symfony\Component\Security\Core\Exception\AuthenticationException;
	use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
	use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
	use Symfony\Component\Security\Http\Event\CheckPassportEvent;
	use Symfony\Component\Security\Http\Event\LoginFailureEvent;
	
	class CheckVerifiedUserSubscriber implements EventSubscriberInterface
	{
		public function __construct(private RouterInterface $router)
		{
		}
		
		public static function getSubscribedEvents()
		{
			return [
				CheckPassportEvent::class => ['onCheckPassword', -10],
				LoginFailureEvent::class => 'onLoginFailure'
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
				throw new AccountNotVerifiedAuthenticationException();
			}
		}
		
		public function onLoginFailure(LoginFailureEvent $loginFailureEvent)
		{
			if (!$loginFailureEvent->getException() instanceof AccountNotVerifiedAuthenticationException) {
				return;
			}
			
			$response = new RedirectResponse(
				$this->router->generate('app_verify_resend_email')
			);
			
			$loginFailureEvent->setResponse($response);
		}
	}