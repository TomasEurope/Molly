FROM alpine:3.19.1
RUN apk --no-cache add php php-pdo_pgsql php-openssl curl
ENTRYPOINT ["sleep", "infinite"]
