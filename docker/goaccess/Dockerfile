# Default goaccess image uses busybox:musl
# which is probably a good thing, but zcat ain't available in it.
# So we "copy" and mod the Dockerfile:
# From https://github.com/allinurl/goaccess/blob/master/Dockerfile
FROM alpine:3

# Make sure `zcat` is available, we need it to read logrotated logs.
RUN apk add --no-cache \
    gzip \
    ;

# latest=1.5.7 at the moment
COPY --from=allinurl/goaccess:latest /bin/goaccess /bin/goaccess

# Not sure about this ; what is it used for?   (output?)
#VOLUME /var/www/goaccess

# Things to override (or not, but we do) in docker-compose.yml
EXPOSE 7890
ENTRYPOINT ["/bin/goaccess"]
CMD ["--help"]
