FROM node:24-trixie AS base
WORKDIR /src

FROM base AS build
RUN apt-get update && apt-get install git
RUN git clone https://github.com/alexprowars/xnova-spa.git .
RUN npm ci --quiet
COPY . .
RUN npm run build -- --quiet

FROM base AS release

ENV NODE_ENV=production
ENV PORT 3000

COPY --from=build /src/.output ./.output
COPY --from=build /src/nuxt.config.ts .
COPY --from=build /src/.env.example .env

EXPOSE ${PORT}

CMD node .output/server/index.mjs