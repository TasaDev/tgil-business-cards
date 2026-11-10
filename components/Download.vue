<template>
  <div id="downloadSection" class="mt-16">
    <h2 class="font-extrabold text-2xl">Publish</h2>
    <div class="stepC mt-6">
      <p class="text-gray-400">Before publishing, confirm:</p>
      <Check
        v-for="(item, index) in downloadCheckList"
        :downloadCheckList="downloadCheckList"
        :item="item"
        :key="index"
        :index="index"
      />
      <div class="flex flex-wrap items-center mt-10">
        <button
          @click="publish"
          :disabled="!canPublish"
          class="leading-none text-2xl tracking-wide font-extrabold p-6 mr-3 rounded select-none transition-colors duration-200 focus:outline-none"
          :class="
            canPublish
              ? 'bg-emerald-600 cursor-pointer text-white focus:bg-emerald-500 hover:bg-emerald-500'
              : 'bg-gray-700 cursor-not-allowed text-gray-400'
          "
        >
          {{ busy ? 'Publishing…' : 'Publish card' }}
        </button>
        <button
          @click="downloadPackage"
          :disabled="!downloadChecked"
          class="leading-none tracking-wide font-extrabold p-4 rounded select-none bg-gray-700 transition-colors duration-200 focus:outline-none"
          :class="
            downloadChecked
              ? 'cursor-pointer text-white hover:bg-gray-600 focus:bg-gray-600'
              : 'cursor-not-allowed text-gray-400'
          "
        >
          Download ZIP
        </button>
      </div>
      <p
        v-if="message"
        role="status"
        class="mt-6 border p-4 rounded"
        :class="error ? 'border-red-600 text-red-400' : 'border-emerald-600'"
      >
        {{ message }}
        <a
          v-if="!error"
          :href="hostedURL"
          target="_blank"
          class="block mt-2 underline font-extrabold text-emerald-500 break-all"
          >{{ hostedURL }}</a
        >
      </p>
      <div
        v-if="published"
        class="mt-6 p-4 rounded border border-gray-700 bg-gray-800 flex flex-col sm:flex-row gap-4 items-center"
      >
        <div
          class="w-36 h-36 shrink-0 p-2 bg-white rounded"
          role="img"
          :aria-label="`QR code for ${publishedURL}`"
          v-html="qrSvg(publishedURL)"
        ></div>
        <div>
          <p class="font-extrabold">Share your card in print</p>
          <p class="text-sm text-gray-400">
            Put the QR code on business cards, desk stands or email
            signatures. Scanning it opens your card.
          </p>
          <div class="mt-3 flex flex-wrap gap-2">
            <button
              type="button"
              @click="downloadQR"
              class="p-3 rounded bg-gray-700 hover:bg-gray-600 font-extrabold transition-colors duration-200 focus:outline-none"
            >
              Download QR (PNG)
            </button>
            <button
              type="button"
              @click="printPoster"
              class="p-3 rounded bg-gray-700 hover:bg-gray-600 font-extrabold transition-colors duration-200 focus:outline-none"
            >
              Print poster / PDF
            </button>
          </div>
        </div>
      </div>
      <p class="mt-6 border p-4 rounded border-gray-700 text-gray-400">
        Your card will be published at {{ hostedURL }}. Publishing again with
        the same name replaces the existing card.
      </p>
    </div>
  </div>
</template>

<script>
import Check from '@/components/Check'
import { saveAs } from 'file-saver'

const esc = (t) =>
  String(t || '').replace(/[&<>"']/g, (c) => `&#${c.charCodeAt(0)};`)

export default {
  props: [
    'downloadCheckList',
    'downloadChecked',
    'downloadPackage',
    'publishPackage',
    'hostedURL',
    'card',
  ],
  components: {
    Check,
  },
  data() {
    return { busy: false, message: '', error: false, published: false, publishedURL: '' }
  },
  computed: {
    canPublish() {
      return this.downloadChecked && !this.busy
    },
  },
  methods: {
    async publish() {
      if (!this.canPublish) return
      this.busy = true
      this.message = ''
      try {
        await this.publishPackage()
        this.error = false
        this.published = true
        this.publishedURL = this.hostedURL
        this.message = 'Published. Your card is live at:'
      } catch (e) {
        this.error = true
        this.published = false
        this.message = e.message
      } finally {
        this.busy = false
      }
    },
    // QRCode comes from /qrcode.min.js, loaded globally in nuxt.config head.
    qrSvg(url) {
      return new window.QRCode({
        content: url,
        container: 'svg-viewbox',
        join: true,
        ecl: 'M',
        padding: 0,
      }).svg()
    },
    async downloadQR() {
      const size = 1024
      const svg = this.qrSvg(this.publishedURL).replace(
        '<svg ',
        `<svg width="${size}" height="${size}" `
      )
      const img = new Image()
      img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg)
      await img.decode()
      const canvas = document.createElement('canvas')
      canvas.width = canvas.height = size + 128 // white quiet zone for scanners
      const ctx = canvas.getContext('2d')
      ctx.fillStyle = '#fff'
      ctx.fillRect(0, 0, canvas.width, canvas.height)
      ctx.drawImage(img, 64, 64, size, size)
      const slug = this.publishedURL.replace(/\/+$/, '').split('/').pop()
      canvas.toBlob((blob) => saveAs(blob, `${slug}-qr.png`))
    },
    // A one-page A6-ish poster; the browser's print dialog can save it as PDF.
    printPoster() {
      const c = this.card
      const w = window.open('', '_blank')
      if (!w) return
      w.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8">
<title>${esc(c.name)} QR</title>
<style>
@page { size: A5; margin: 12mm; }
body { font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; color: #14213d;
  display: flex; flex-direction: column; align-items: center; text-align: center; margin: 0; padding: 24px; }
img { max-height: 70px; max-width: 60%; }
h1 { margin: 24px 0 4px; font-size: 28px; }
.ar { font-size: 22px; font-weight: 700; margin: 4px 0; }
.t { margin: 0; color: #204080; font-weight: 600; }
.qr { width: 260px; height: 260px; margin: 28px 0 12px; }
.u { font-size: 14px; color: #56627a; word-break: break-all; }
.s { margin-top: 4px; font-weight: 600; }
</style></head><body>
<img src="${esc(window.location.origin + c.logo)}" alt="${esc(c.biz)}">
<h1>${esc(c.name)}</h1>
${c.arName ? `<p class="ar" dir="rtl" lang="ar">${esc(c.arName)}</p>` : ''}
<p class="t">${esc(c.title)}</p>
<div class="qr">${this.qrSvg(this.publishedURL)}</div>
<p class="s">Scan to save my contact</p>
<p class="u">${esc(this.publishedURL)}</p>
<script>window.onload = () => window.print()<\/script>
</body></html>`)
      w.document.close()
    },
  },
}
</script>
