<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\ChannelContext;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RequestAttributeBasedChannelContext implements ChannelContextInterface
{
    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(ChannelRepositoryInterface $channelRepository, RequestStack $requestStack)
    {
        $this->channelRepository = $channelRepository;
        $this->requestStack = $requestStack;
    }

    public function getChannel(): ChannelInterface
    {
        try {
            return $this->getChannelForRequest($this->getMasterRequest());
        } catch (\UnexpectedValueException $exception) {
            throw new ChannelNotFoundException($exception);
        }
    }

    private function getChannelForRequest(Request $request): ChannelInterface
    {
        $channelCode = $request->attributes->get('channelCode');
        if (null === $channelCode) {
            throw new \UnexpectedValueException('Channel was not found for given request');
        }

        $channel = $this->channelRepository->findOneByCode($channelCode);
        if (null === $channel) {
            throw new \UnexpectedValueException('Channel was not found for given request');
        }

        return $channel;
    }

    private function getMasterRequest(): Request
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        if (null === $masterRequest) {
            throw new \UnexpectedValueException('There are not any requests on request stack');
        }

        return $masterRequest;
    }
}
