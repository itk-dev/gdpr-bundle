# GDPR Bundle

## Installation

```sh
composer require itk-dev/gdpr-bundle "^1.0"
```

Enable the bundle in `app/AppKernel.php`:

```php
public function registerBundles() {
	$bundles = [
		// …
        new ItkDev\GDPRBundle\ItkDevGDPRBundle(),
	];
    // …
}
```

Check default bundle configuration

```sh
bin/console config:dump-reference ItkDevGDPRBundle
```

If the default configuration do not match your setup it can be modified in `app/config/config.yml`.