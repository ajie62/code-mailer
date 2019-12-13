<?php

namespace App\Service;

use App\Entity\User;
use Knp\Snappy\Pdf;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\NamedAddress;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Environment;

class Mailer
{
    /** @var MailerInterface */
    private $mailer;

    /** @var Environment */
    private $twig;

    /** @var Pdf */
    private $pdf;

    /** @var EntrypointLookupInterface */
    private $entrypointLookup;

    public function __construct(MailerInterface $mailer, Environment $twig, Pdf $pdf, EntrypointLookupInterface $entrypointLookup)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->pdf = $pdf;
        $this->entrypointLookup = $entrypointLookup;
    }

    public function sendWelcomeMessage(User $user)
    {
        $email = (new TemplatedEmail())
            ->from(new NamedAddress('alienmailer@example.com', 'The Space Bar'))
            ->to(new NamedAddress($user->getEmail(), $user->getFirstName()))
            ->subject('Welcome to the Space Bar!')
            ->htmlTemplate('email/welcome.html.twig')
            ->context([
                // 'user' => $user
            ]);

        $this->mailer->send($email);
    }

    public function sendAuthorWeeklyReportMessage(User $author, array $articles)
    {
        $html = $this->twig->render('email/author-weekly-report-pdf.html.twig', [
            'articles' => $articles,
        ]);

        $this->entrypointLookup->reset();

        $pdf = $this->pdf->getOutputFromHtml($html);

        $email = (new TemplatedEmail())
            ->from(new NamedAddress('alienmailer@example.com', 'The Space Bar'))
            ->to(new NamedAddress($author->getEmail(), $author->getFirstName()))
            ->subject('Your weekly report on The Space Bar!')
            ->htmlTemplate('email/author-weekly-report.html.twig')
            ->context([
                'author' => $author,
                'articles' => $articles
            ])
            ->attach($pdf, sprintf('weekly-report-%s.pdf', date('Y-m-d')));

        $this->mailer->send($email);
    }
}
