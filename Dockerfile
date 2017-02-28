FROM mojdigital/wordpress-base:latest

ADD . /bedrock

WORKDIR /bedrock

RUN chmod +x build.sh && \
	sleep 1 && \
	./build.sh
