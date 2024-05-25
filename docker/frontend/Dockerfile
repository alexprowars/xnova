FROM node:20-alpine as base
WORKDIR /src

FROM base AS build
RUN apk update && apk add git
RUN git clone https://github.com/alexprowars/xnova-spa.git .
RUN npm ci --quiet
COPY . .
RUN npm run build -- --quiet

FROM base AS release

ENV NODE_ENV=production
ENV PORT 3200

COPY --from=build /src/.output ./.output
COPY --from=build /src/nuxt.config.ts .
COPY --from=build /src/.env.example .env

CMD node .output/server/index.mjs