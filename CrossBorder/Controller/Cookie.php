<?php
namespace Transiteo\CrossBorder\Controller;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Cookie
{
    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Get form key cookie
     *
     * @return string
     */
    public function get($cookie)
    {
        return $this->cookieManager->getCookie($cookie);
    }

    /**
     * @param string $value
     * @param int $duration
     * @return void
     */
    public function set($cookie, $value, $duration = 86400)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setHttpOnly(true)
            ->setDuration($duration)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain()
            );

        $this->cookieManager->setPublicCookie(
            $cookie,
            $value,
            $metadata
        );
    }

    /**
     * @return void
     */
    public function delete($cookie)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->deleteCookie(
            $cookie,
            $metadata
        );
    }
}
