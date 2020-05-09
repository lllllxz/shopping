<?php

return [
    'alipay' => [
        'app_id'         => '2016101800712939',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApLaWky6TcdXXZTNqXa23/vZq7Z4hwoqdWgUGqcAsl2gmx31u23C8/80O82RoegkITAiqOnNOKFHYan5nw8ar17HoCOJqdbsyPbaAx5QIcDQM2e8xCQFNOOfdViuJWzrdttlVd2vd+q9mXqB/F25PSpdp92oPvCwiq2RHnsqJqxHS6WtqQ1LJgZkwdKh+8mwnM4Hfj/wJJ7siasE8YdRaqdTUimSswr5XD4Hz/95TQNPKPxOLW5P94yN4FOdjcQp9sGYjtWis/Ihlm8ElJErXfkwrvjKbHzqFJUmkybXuME8bIy73hag0pzeHSMY/oRKBzMoKOKwTCDoezPu9wwt1uwIDAQAB',
        'private_key'    => 'MIIEowIBAAKCAQEAl9wDAxPuIptdjyiOCxzPP9AGCmNRbSVdKXW6mmEjuGSRxfaPh86vv1axmKcVef+KWkciq0tVSPOpRnh4FqBT2K1v5OMl+eOj29cFowIJ/SJqwggs1s3wg6FALQya6ijPm3AR290fFIXwyUEOmR8SkpJ+signW6KsyzvP6dGBvXz0kd+/n0hY55RPTLYjZiHlUzA/AFSuvp3YWG8InGuJQQdzJFrAWJAq5nBBHlcr/W4sEzcsZo3OfFM86Z1c2dbtJl6xOxtbynz/tBvY3b2W7PKHlPd6xHOOIUCn4I48+0GUXkc3m6MuIH16trZbSdaWfGNue0/ZV0UswJCEcSe9hQIDAQABAoIBAB2S6DdCO18b+LV3hWoemzHnNjXxr5rc6u63Ebcc8dLoKwdtg/hDxTAzFvUOnP0cSfpY3iST0DEb2rxxm5l0cb/Bzwe5QN0QewnGLz/WltFoUXgmFW1jv9Iypgff649skTnJMoEp5/KcPw9CjzA8v+Yxh2D0tu1+mb4ekNgNSAH5s3jvuIssHFYS2VCEquNV3d61IC0OCmLWIf5ZGLwk6wXEPk6RBdaTTr49JCJSIq/cmS+SJnELkHnDEXBN6kMGfpQrRAprZfYS6QZ6v+nsJPdqKGkNLHp2HOC9PXBKNyZeKB+OI4nuVG2sfIA50DECllVZNYNlkqanmUGlORyzZyUCgYEA/dPCrTllG0KCzz9GafSBoRzi08ztfUy/WzuriZn4EOzHSP8jlE7rftfcDbDyWMt5eQEBFBrupXxF/tyxtfQ1U/qFr5D0F2g0DqFE+3hbLy8jTpbkwyrHYjXN6hPB9OxwfgW5NDEeo+z8MsbIeTcLAYoHFZzW7BEpJNAVOXXYKMMCgYEAmSjMTw4ze4o/Q8VZMO3evjF38ap2gjNgyey+OB6t+c9H7PPJOeK/dpJILHU7KhYcEoYPOOffjk53r6Gfj4fYUw4qPLOcqKoTNFH3XvUEt3XwcTDUAVxdDltzGVBdfg/0Mu8vVnmLNkIrSfgGvyLOUqgI+Al5lnp50uvWrpIyXBcCgYAl6MaekH/lPl3DDjQ6BuaFZYcLEwQ1Po0l1xebiX9fJ73rzQ9HSzIo05xt/wty75DI3bmHgy45UQIzOkrgXgTI8hWdTwzlog6EUNm4pRUZSvT++9JWw8DbjiWe3CyPo/B5IemzPdLRsMdJ3h563BmstSyxsab3wuheMyW4Wz1ZeQKBgAFrv3l8SD39KlkWm504l7hr/RDg4/iRQXSeHaWnozWOFry8BkHjOKOA9+pxq/rX+aqyU7HTdN99Gt8mQLS0Le2XVAz8HZfy+/qgSAs5erG5BmIGcfERSku3zXmOpU9mFn3iei3zMrduJbip9GYRjJh3tp2WeOpMeJTcW0GDWbRBAoGBAKgLosHTwOQ+bmHr9+KF+KlilKrPM/d/GLUP/WOVDmZxWKYanXWirb+FT58xpehCA0MZXBo+I6AwWPKu4dTSGXZ4aQu/F3nqZGLxrpNVk+KAR/kkLUQkwtjbzS9wgV6ArWO6uDtJ2TeJ36Y4YH4BU1oeAgZUGz+PDMI8EMD5Fk4r',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ]
    ],
    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ]
];
