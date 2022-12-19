# i2t Smart

## Introduction

The propuse of this Drupal module is to extend the current functionality
provided by the i2t Smart devices to be controlled by voice commands third party
devices such as Siri, Google Assistant or Alexa, please read to the official
documentation at https://i2t.com.mx/ayuda-configuracion-control-por-voz/ before
use this module.

In order to use the voice commands works you will need to create an applet on
the IFTTT site https://ifttt.com/, this applet will send a web request to the
i2t servers to enable/disable your i2t device, however such requests works as a
toggle, this means that if you send a web request to the i2t servers your device
will be enabled but if you send the web request again your device will be
disabled and vice-versa.

The purpose of this module is to create a bridge between your Drupal site and
the i2t server in order to create separate URLs: one for enable your device and
another for disable it.

## How does it work?
Currently, if you give Alexa the instruction "Enciende Alarma Vecinal" it will
enable your device, but if you say it again Alexa will disable your device.

This module will create two different URLs one for Enable your device and
other to disable it, with this module you can create two separated actions on
IFTTT, one to enable your device and other for disable it, this module will
check if your device is disabled and then will enable it, if the device is
already enabled the module will take no action and won't disable your device and
vice-versa.

### Use cases
For instance, if you send the voice command `Enciende Alarma Vecinal` to Alexa
and then other member of your family issues then the same command the alarm will
stay enabled. On the current i2t implementation this actual does not happen
because the second command will disable your alarm.

Other example is: if you want to create an automated tasks to enable your
electric fence at dusk and disable it at dawn then this automated task will work
as intended regardless if you manually disable or enable your fence.

## Configuration
Make sure to get your API key from i2t, it should be something like:
`aBCDefghijKLMnopqrstuvwxyzAbCdef`

Go to `/admin/config/i2t` and insert your API Key

### Applet to ENABLE your fence

On IFTTT create an applet to enable your fence (see https://i2t.com.mx/ayuda-configuracion-control-por-voz/ )

On the `Web hook > Make a web request` use this values

URL: `https://mydrupalsite.com/api/i2t/fence/enable`

Method: `GET`

Content Type: `text/plain`

Additional Headers `x-api-key:aBCDefghijKLMnopqrstuvwxyzAbCdef`
#### Note: use your actual API Key, the above is just an example, also replace mydrupalsite.com with your actual site address.

### Applet to DISABLE your fence

On IFTTT create an applet to disable your fence (see https://i2t.com.mx/ayuda-configuracion-control-por-voz/ )

On the `Web hook > Make a web request` use this values

URL: `https://mydrupalsite.com/api/i2t/fence/disable`

Method: `GET`

Content Type: `text/plain`

Additional Headers `x-api-key:aBCDefghijKLMnopqrstuvwxyzAbCdef`
#### Note: use your actual API Key, the above is just an example, also replace mydrupalsite.com with your actual site address.