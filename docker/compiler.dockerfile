FROM alpine:latest

ENV LANG=C.UTF-8 JAVA_HOME=/usr/lib/jvm/java-17-openjdk PATH=$PATH:$JAVA_HOME/bin

RUN apk update && apk add --no-cache \
    bash \
    build-base \
    curl \
    wget \
    openjdk17 \
    llvm17 \
    llvm17-libs \
    clang17 \
    python3 \
    py3-pip \
    binutils \
    libstdc++ \
    ncurses \
    gmp \
    readline \
    boost-dev \
    gmp-dev \
    mpfr-dev \
    eigen

# Set default clang/clang++ version to Clang 17
RUN ln -s /usr/bin/clang-17 /usr/bin/clang && \
    ln -s /usr/bin/clang++-17 /usr/bin/clang++

# Install Free Pascal Compiler
RUN wget https://sourceforge.net/projects/freepascal/files/Linux/3.2.2/fpc-3.2.2.x86_64-linux.tar && \
    tar -xf fpc-3.2.2.x86_64-linux.tar && \
    cd fpc-3.2.2.x86_64-linux && \
    ./install.sh && \
    cd .. && rm -rf fpc-3.2.2.x86_64-linux.tar fpc-3.2.2.x86_64-linux

# Add a non-root user for secure execution
RUN adduser -D executor
USER executor
WORKDIR /home/executor