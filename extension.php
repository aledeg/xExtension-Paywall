<?php

class PaywallExtension extends Minz_Extension {
    public function init(): void {
        $this->registerTranslates();

        if (version_compare(FRESHRSS_VERSION, 1.28) >= 0) {
            $this->registerHook(Minz_HookType::EntryBeforeDisplay, [$this, 'markAsBehindAPaywall']);
        } else {
            $this->registerHook('entry_before_display', [$this, 'markAsBehindAPaywall']);
        }
    }
    
    public function handleConfigureAction(): void {
        $this->registerTranslates();

        if (Minz_Request::isPost()) {
            $domains = Minz_Request::paramTextToArray('domains', '');
            sort($domains);
            $configuration = [
                'domains' => $domains,
                'title_prefix' => Minz_Request::paramString('title_prefix', ''),
            ];
            $this->setUserConfiguration($configuration);
        }
    }

    /**
     * @param \FreshRSS_Entry $entry
     * @return \FreshRSS_Entry
     */
    public function markAsBehindAPaywall($entry) {
        foreach ($this->getDomainsFromConfiguration() as $domain) {
            if (mb_stripos($entry->link(), $domain) !== false) {
                $entry->_title($this->getTitlePrefix().' '.$entry->title());
                break;
            }
        }

        return $entry;
    }

    private function getDomainsFromConfiguration(): array {
        return $this->getUserConfigurationValue('domains') ?? [];
    }

    public function getDomains(): string {
        return implode(PHP_EOL, $this->getDomainsFromConfiguration());
    }

    public function getTitlePrefix(): string {
        return $this->getUserConfigurationValue('title_prefix') ?? '';
    }
}
