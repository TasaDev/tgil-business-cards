<template>
  <div
    class="overflow-y-scroll max-hd border-t-0 border-4 border-black bg-gray-900"
  >
    <div id="cardPreview">
      <html
        ref="html"
        lang="en"
        :style="cssVars"
      >
        <head>
          <meta charset="UTF-8" />
          <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
          />
          <meta v-if="!PreviewMode" name="robots" content="noindex, nofollow" />
          <meta name="author" :content="genInfo.biz" />
          <meta
            property="og:title"
            :content="`${getFullname}'s Digital Business Card`"
          />
          <meta
            property="twitter:title"
            :content="`${getFullname}'s Digital Business Card`"
          />
          <!-- prettier-ignore -->
          <script>
            "http"==window.location.href.substr(0,4)&&"/"!=window.location.href.slice(-1)&&window.location.replace(window.location.href+"/");
          </script>

          <link v-if="getCssHref" :href="getCssHref" rel="stylesheet" />
          <title>{{ getFullname }}'s Digital Business Card</title>
          <style v-if="getCssHref">
            #body{
            {{genInfo.fontCss && getFontFamily}}
            }
          </style>
        </head>
        <body id="body" :class="`t-${theme}`">
          <div
            id="modal"
            ref="modal"
            :style="`backgroundColor: ${colors.mainBg.color}; visibility: hidden; top: 2rem; opacity: 0;`"
          >
            <a id="close" @click="closePublicKey()" class="closeColor" aria-label="Close">
              <div
                class="icon ink"
                v-html="require(`~/assets/icons/close.svg?include`)"
              ></div>
            </a>
            <div id="keyView">
              <p class="textColor">
                Use my public key to send me encrypted messages
              </p>
              <a
                :href="!PreviewMode && `./${getFullname}'s public key.asc`"
                download
                target="_blank"
                id="dlKey"
                rel="noreferrer"
                @click.prevent.capture="downloadKey()"
                :style="{
                  backgroundColor: `${colors.buttonBg.color}`,
                }"
                tabindex="-1"
              >
                <div
                  class="icon iconColor"
                  v-html="require(`~/assets/icons/download.svg?include`)"
                ></div>
                <span class="iconColor">Download Key</span>
              </a>
            </div>
            <div id="copyView" ref="copyView">
              <p class="textColor">
                Copy and send the URL to share my Business Card
              </p>
              <button
                id="copyURL"
                :style="{
                  backgroundColor: `${colors.buttonBg.color}`,
                }"
              >
                <div
                  class="icon iconColor"
                  v-html="require(`~/assets/icons/copy.svg?include`)"
                ></div>
                <span class="iconColor">Copy URL</span>
              </button>
            </div>
            <div id="qrView" ref="qrView" class="textColor">
              <div id="qr"></div>
              <h3>Scan the QR Code</h3>
              <p>to view my Business Card on another device</p>
            </div>
          </div>
          <header>
            <div
              id="topActions"
              :style="{ display: PreviewMode ? 'flex' : 'none' }"
            >
              <div>
                <a id="share" aria-label="Share" @click.prevent.capture="sharingAlert()">
                  <div
                    class="icon topAction ink"
                    v-html="require(`~/assets/icons/share.svg?include`)"
                  ></div>
                </a>
                <a id="showQR" aria-label="Show QR code" @click.prevent.capture="sharingAlert()"
                  ><div
                    class="icon topAction ink"
                    v-html="require(`~/assets/icons/qrcode.svg?include`)"
                  ></div>
                </a>
              </div>
              <a
                v-if="pubKeyIsValid"
                id="showKey"
                @click.prevent.capture="showKey()"
                ><div
                  class="icon topAction ink"
                  v-html="require(`~/assets/icons/key.svg?include`)"
                ></div>
              </a>
            </div>
            <div class="headerImgC">
              <img
                id="cover"
                v-if="images.cover.url"
                :src="
                  PreviewMode ? images.cover.url : `./cover.${images.cover.ext}`
                "
                alt="Background Pattern"
              />
              <img
                id="logo"
                v-if="images.logo.url"
                :src="
                  PreviewMode ? images.logo.url : `./logo.${images.logo.ext}`
                "
                :alt="genInfo.biz || 'Logo'"
              />
            </div>
          </header>
          <main
            :style="{ marginTop: `${hasOnlyProfilePic ? '5rem' : '0'}` }"
          >
            <img
              id="profilePhoto"
              v-if="images.photo.url"
              :src="
                PreviewMode ? images.photo.url : `./photo.${images.photo.ext}`
              "
              alt="Photo"
            />
            <div id="info" class="textColor">
              <p class="name">
                {{ getFullname }}
              </p>
              <p v-if="genInfo.pronouns" class="pronouns">
                ({{ genInfo.pronouns }})
              </p>
              <p class="jobtitle">
                {{ genInfo.title }}
              </p>
              <p v-if="genInfo.arName || genInfo.arTitle" class="arabic" dir="rtl" lang="ar">
                <span v-if="genInfo.arName" class="arName">{{ genInfo.arName }}</span>
                <span v-if="genInfo.arTitle" class="arTitle">{{ genInfo.arTitle }}</span>
              </p>
              <p class="bizname">
                {{ genInfo.biz }}
              </p>
              <p class="bizaddr" v-if="genInfo.addr">
                {{ genInfo.addr }}
              </p>
            </div>
            <p class="sub textColor" v-if="genInfo.desc">
              {{ genInfo.desc }}
            </p>
            <a
              id="cta"
              rel="noreferrer"
              :href="!PreviewMode && `${username}.vcf`"
              download
              target="_blank"
              @click.prevent="downloadVcard"
            >
              <div
                class="icon ink"
                v-html="require(`~/assets/icons/add-user.svg?include`)"
              ></div>
              <p>Save Contact</p>
            </a>
            <div class="actions" v-if="primaryActions.length">
              <a
                v-for="(item, index) in primaryActions"
                :key="'pa' + index"
                class="actionBtn"
                :href="getHref(item)"
                target="_blank"
                rel="noopener noreferrer"
              >
                <span
                  class="actionIcon icon-wrap ink"
                  ><span
                    class="icon"
                    v-html="require(`~/assets/icons/${item.icon}.svg?include`)"
                  ></span
                ></span>
                <span class="actionText">
                  <span class="actionLabel">{{ item.name }}</span>
                  <span class="actionValue" v-if="item.value">{{
                    item.value
                  }}</span>
                </span>
              </a>
            </div>
            <div class="secondary" v-if="secondaryActions.length">
              <a
                v-for="(item, index) in secondaryActions"
                :key="'sa' + index"
                class="secBtn"
                :href="getHref(item)"
                target="_blank"
                rel="noopener noreferrer"
                :style="{ background: item.color }"
                :aria-label="item.name"
              >
                <span class="icon" v-html="getSVG(item)"></span>
              </a>
            </div>
            <div
              class="featured"
              v-for="(item, index) in featured"
              :key="'fc' + index"
            >
              <h2 class="section textColor" v-if="item.title && item.content.length">
                {{ item.title }}
              </h2>
              <div v-for="(item, i) in item.content" :key="i">
                <div
                  v-if="item.contentType == 'media'"
                  class="media"
                  :class="item.type"
                  :style="{ backgroundColor: `${colors.cardBg.color}` }"
                >
                  <div v-if="item.type == 'image'">
                    <img
                      v-if="item.dataURI"
                      :src="
                        PreviewMode
                          ? item.dataURI
                          : `./media/${getTitle(item.title)}.${item.ext}`
                      "
                      alt="Product image"
                    />
                    <div class="controls cardColor">
                      <p class="title">
                        {{ item.title }}
                      </p>
                    </div>
                  </div>
                  <MediaPlayer
                    v-if="item.type == 'music' || item.type == 'video'"
                    ref="mediaPlayer"
                    :media="item"
                    :type="item.type"
                    :colors="colors"
                    :togglePlay="togglePlay"
                    :PreviewMode="PreviewMode"
                  />
                  <DocumentDownloader
                    v-if="item.type == 'document'"
                    :media="item"
                    :type="item.type"
                    :colors="colors"
                    :PreviewMode="PreviewMode"
                  />
                </div>
                <ProductShowcase
                  v-else-if="item.contentType == 'product' && item.title"
                  :product="item"
                  :colors="colors"
                  :PreviewMode="PreviewMode"
                />
                <div
                  v-else-if="item.contentType == 'text' && item.value"
                  class="media"
                  :style="{ backgroundColor: `${colors.cardBg.color}` }"
                >
                  <p class="textC cardColor">{{ item.value }}</p>
                </div>
                <div
                  v-else-if="stripAttr(item)"
                  class="media embedded"
                  :style="{ backgroundColor: `${colors.cardBg.color}` }"
                >
                  <iframe
                    :src="stripAttr(item)"
                    frameborder="0"
                    allowfullscreen
                  ></iframe>
                </div>
              </div>
            </div>
          </main>
          <footer
            v-if="footerCredit"
            :style="{ backgroundColor: `${colors.mainBg.color}` }"
            class="textColor"
          >
            Created with
            <a
              class="textColor"
              href="https://enbizcard.vishnuraghav.com/"
              target="_blank"
              rel="noopener noreferrer"
              >EnBizCard</a
            >
          </footer>
        </body>
      </html>
    </div>
  </div>
</template>

<script>
import MediaPlayer from './MediaPlayer'
import DocumentDownloader from './DocumentDownloader'
import ProductShowcase from './ProductShowcase'
import utils from '@/mixins/utils'
import { mapState } from 'vuex'

export default {
  props: [
    'username',
    'genInfo',
    'images',
    'featured',
    'colors',
    'primaryActions',
    'secondaryActions',
    'PreviewMode',
    'downloadVcard',
    'downloadKey',
    'footerCredit',
    'showAlert',
    'hasLightBG',
    'pubKeyIsValid',
  ],
  mixins: [utils],
  components: {
    MediaPlayer,
    DocumentDownloader,
    ProductShowcase,
  },
  watch: {
    getFeaturedMusic(oldv, newv) {
      this.paused = this.getFeaturedMusic.map((e) => true)
    },
  },
  data() {
    return {
      paused: [],
      hasInstagramEmbed: false,
    }
  },
  computed: {
    ...mapState(['theme']),
    getFullname() {
      let fn = this.genInfo.fname
      let ln = this.genInfo.lname
      return (fn + ln).length ? `${fn ? fn : ''}${ln ? ' ' + ln : ''}` : null
    },
    // Brand colours as CSS variables; card.scss themes derive everything from these.
    cssVars() {
      const c = this.colors
      return `--accent:${c.buttonBg.color};--header:${c.logoBg.color};--page:${c.mainBg.color};--surface:${c.cardBg.color}`
    },
    hasOnlyProfilePic() {
      return !(this.images.cover.url || this.images.logo.url)
    },
    getFeaturedMusic() {
      return this.featured.music
    },
    getCssHref() {
      if (this.genInfo.fontLink) {
        let html = new DOMParser().parseFromString(
          this.genInfo.fontLink,
          'text/html'
        )
        let link = Array.from(html.getElementsByTagName('link')).filter(
          (e) => e.getAttribute('rel') == 'stylesheet'
        )
        return link.length && link[0].getAttribute('href')
      }
      return false
    },
    getFontFamily() {
      let regex = /^font-family[^;]*/
      let css = this.genInfo.fontCss.replace(/\s+/, '')
      if (regex.test(css)) {
        return css.match(/^font-family[^;]*/)[0]
      }
    },
  },
  methods: {
    getHref(e) {
      let value = null
      if (e.name === 'Viber' && e.value)
        value = e.value.replace(/[\s\-()]/g, '').replace(/\+/, '%2B')
      return e.href
        ? e.href + (value || e.value) + (e.hrefEnd ? e.hrefEnd : '')
        : value || e.value
    },
    getTitle(e) {
      return e.toLowerCase().split(' ').join('_')
    },
    stripAttr(val) {
      if (/<iframe(.*)\/iframe>/.test(val)) {
        let iframe = val.match(/<iframe(.*)\/iframe>/)[0]
        return iframe.match(/src="?([^"\s]+)"/)[1]
      } else if (/\/\/www\.instagram\.com\/embed\.js/.test(val)) {
        return `${
          val.match(/data-instgrm-permalink="(.*?)\/\?/)[1]
        }/embed/captioned`
      }
      return null
    },
    toggleContainer(e) {
      '2rem' == e.style.top
        ? ((e.style.visibility = 'visible'),
          (e.style.top = '0px'),
          (e.style.opacity = 1))
        : ((e.style.top = '2rem'),
          (e.style.opacity = 0),
          setTimeout(() => {
            e.style.visibility = 'hidden'
          }, 200))
    },
    showKey() {
      let modal = this.$refs.modal
      let copyView = this.$refs.copyView
      let qrView = this.$refs.qrView
      this.toggleContainer(modal)
      copyView.style.display = qrView.style.display = 'none'
    },
    closePublicKey() {
      let modal = this.$refs.modal
      this.toggleContainer(modal)
    },
    sharingAlert() {
      this.showAlert(
        'Sharing works once your card is published.'
      )
    },
    togglePlay(ref) {
      let mediaPlayers = this.$refs.mediaPlayer
      mediaPlayers.forEach((e) => {
        let mediaSource = e.$refs.mediaSource
        let play = e.$refs.play
        let pause = e.$refs.pause
        if (ref != mediaSource) {
          mediaSource.pause()
          play.style.display = 'block'
          pause.style.display = 'none'
        } else {
          if (mediaSource.paused) {
            mediaSource.play()
            play.style.display = 'none'
            pause.style.display = 'block'
          } else {
            mediaSource.pause()
            play.style.display = 'block'
            pause.style.display = 'none'
          }
        }
      })
    },
  },
  mounted() {
    setTimeout(() => {
      this.hasInstagramEmbed = true
    }, 5000)
  },
}
</script>

<style lang="scss">
// Same stylesheet as the exported card, scoped to the preview pane.
#cardPreview {
  @import '../assets/styles/card';
}
</style>
