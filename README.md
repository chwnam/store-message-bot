# 메시지 저장 봇

내 메시지를 서버에 받아 놓는 텔레그램 봇

휴대전화 등에서 '공유' 기능을 통해 웹사이트의 링크나 여러 정보들을 텔레그램으로 간편하고 빠르게 전송하고,
이 정보를 받아 내 서버에서 적절히 정보를 가공해 저장하는 봇.

이렇게 저장된 정보는 클라우드를 통해 여러 장치에 걸쳐 동기화되고,
옵시디언 같은 노트 앱에서 매우 편리하게 저장된 링크를 편집할 수 있도록 한다.


## 설정법

봇 토큰을 받은 다음 환경 변수에 `BOT_TOKEN` 을 설정합니다.

```shell
# .bashrc
export BOT_TOKEN=1234568901:API_BOT_TOKEN_RANDOM_STRINGS
```

또는 .dotenv 파일에 저장해도 무방합니다.

우선 템플릿 파일을 복사한 후,
```shell
cp dotenv.dist .env
```

봇 토큰과 저장할 경로를 저장해 주면 됩니다.
```dotenv
# 봇 토큰. 반드시 지정해야 합니다.
BOT_TOKEN=1234568901:API_BOT_TOKEN_RANDOM_STRINGS

# 메시지 저장 경로. 반드시 지정해야 합니다.
STORE_PATH=/path/to/message.md
```

## 실행하기

경로를 잘 설정한 다음, `composer run` 으로 실행합니다.
