<template>
  <div
    ref="container"
    class="container relative bg-gray-900 mx-auto text-gray-100"
    style="max-width: 960px"
  >
    <Modal
      v-if="content"
      @click.native.self="clearContent"
      :content="content"
      :clearContent="clearContent"
    />
    <transition name="drop">
      <div
        v-if="inView || showPreview"
        class="
          fixed
          top-0
          w-full
          z-30
          bg-gray-900
          justify-between
          items-center
          flex
          md:hidden
        "
      >
        <img :src="brand.logo" :alt="brand.name" class="h-10 w-auto m-4 p-1 bg-white rounded" />
        <button
          class="
            p-3
            mx-4
            font-extrabold
            rounded
            tracking-wide
            focus:outline-none
            select-none
          "
          :class="showPreview ? 'bg-gray-700' : 'bg-emerald-600'"
          @click="!opening && togglePreview()"
        >
          {{ showPreview ? 'Close preview' : 'Open preview' }}
        </button>
      </div>
    </transition>
    <transition name="fade">
      <Preview
        v-show="showPreview"
        class="fixed top-20 w-full bottom-0 z-20 border-none rounded-b-none"
        ref="html"
        :username="username"
        :genInfo="genInfo"
        :images="images"
        :featured="featured"
        :colors="colors"
        :primaryActions="primaryActions"
        :secondaryActions="secondaryActions"
        :PreviewMode="PreviewMode"
        :downloadVcard="downloadVcard"
        :footerCredit="footerCredit"
        :showAlert="showAlert"
        :hasLightBG="hasLightBG"
        :downloadKey="downloadKey"
        :pubKeyIsValid="pubKeyIsValid"
      />
    </transition>

    <header class="px-4 pt-8 flex items-center gap-4">
      <img :src="brand.logo" :alt="brand.name" class="h-14 w-auto p-2 bg-white rounded" />
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Business card maker</h1>
        <p class="text-gray-400">Fill in your details, check the preview, then publish.</p>
      </div>
      <nav class="ml-auto flex flex-wrap justify-end gap-2 shrink-0">
        <a
          href="/team/"
          target="_blank"
          class="p-3 rounded bg-gray-700 hover:bg-gray-600 font-extrabold transition-colors duration-200"
          >Staff directory</a
        >
        <a
          href="/logout.php"
          class="p-3 rounded border border-gray-500 hover:bg-gray-700 font-extrabold transition-colors duration-200"
          >Sign out</a
        >
      </nav>
    </header>
    <div class="md:grid md:grid-cols-2">
      <div class="px-4 mt-4">
        <div class="mt-6 p-4 rounded border border-gray-700 bg-gray-800">
          <label for="edit-slug" class="font-extrabold">Already have a card?</label>
          <p class="text-sm text-gray-400">
            Enter its address to load it, change what you need, then publish
            again.
          </p>
          <div class="mt-2 flex gap-2">
            <span class="self-center text-gray-400 shrink-0 hidden sm:inline"
              >{{ origin }}/</span
            >
            <input
              id="edit-slug"
              v-model="editSlug"
              spellcheck="false"
              autocapitalize="none"
              placeholder="ashakhan"
              @keypress.enter="loadCard"
              class="flex-1 min-w-0 px-4 h-12 bg-black rounded border border-transparent focus:outline-none"
            />
            <button
              type="button"
              @click="loadCard"
              :disabled="loading"
              class="px-5 h-12 rounded bg-gray-700 hover:bg-gray-600 font-extrabold transition-colors duration-200 focus:outline-none"
            >
              {{ loading ? 'Loading…' : 'Load' }}
            </button>
          </div>
          <p
            v-if="editMsg"
            role="status"
            class="mt-2 text-sm"
            :class="editError ? 'text-red-400' : 'text-gray-200'"
          >
            {{ editMsg }}
          </p>
        </div>
        <div ref="create" id="step-2" class="pt-8">
          <h2 class="font-extrabold text-2xl">Your details</h2>
          <Attachment
            :content="images"
            type="photo"
            :resizeImage="resizeImage"
            label="Add profile photo"
            description="suggested format: jpeg, png or gif"
            :showAlert="showAlert"
          />
          <p class="mt-6 border p-4 rounded border-gray-700 text-gray-400">
            Recommended profile photo size is 320 x 320 pixels, with an aspect
            ratio of 1:1
          </p>
          <div class="stepC mt-6 grid grid-cols-2 gap-4">
            <div>
              <label for="firstname" class="ml-4">First name</label>
              <input
                id="firstname"
                spellcheck="false"
                type="text"
                v-model="genInfo.fname"
                autocapitalize="words"
                class="
                  mt-2
                  px-4
                  w-full
                  h-12
                  bg-black
                  rounded
                  border border-transparent
                  transition-colors
                  duration-200
                  focus:outline-none focus:border-gray-600
                  hover:border-gray-600
                "
              />
            </div>
            <div>
              <label for="lastname" class="ml-4">Last name</label>
              <input
                id="lastname"
                spellcheck="false"
                type="text"
                v-model="genInfo.lname"
                autocapitalize="words"
                class="
                  mt-2
                  px-4
                  w-full
                  h-12
                  bg-black
                  rounded
                  border border-transparent
                  transition-colors
                  duration-200
                  focus:outline-none focus:border-gray-600
                  hover:border-gray-600
                "
              />
            </div>
          </div>
          <div class="stepC mt-6">
            <label for="pronouns" class="ml-4">Gender pronouns</label>
            <input
              id="pronouns"
              spellcheck="false"
              type="text"
              v-model="genInfo.pronouns"
              placeholder="He/Him/His"
              autocapitalize="words"
              class="
                mt-2
                px-4
                w-full
                h-12
                bg-black
                placeholder-gray-600
                rounded
                border border-transparent
                transition-colors
                duration-200
                focus:outline-none focus:border-gray-600
                hover:border-gray-600
              "
            />
          </div>
          <div class="stepC mt-6">
            <label for="job-title" class="ml-4">Job title</label>
            <input
              id="job-title"
              type="text"
              spellcheck="true"
              autocapitalize="words"
              v-model="genInfo.title"
              class="
                mt-2
                px-4
                w-full
                h-12
                bg-black
                rounded
                border border-transparent
                transition-colors
                duration-200
                focus:outline-none focus:border-gray-600
                hover:border-gray-600
              "
            />
          </div>
          <div class="stepC mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="ar-name" class="ml-4"
                >Name in Arabic <span class="text-gray-400">optional</span></label
              >
              <input
                id="ar-name"
                type="text"
                dir="rtl"
                lang="ar"
                v-model="genInfo.arName"
                class="mt-2 px-4 w-full h-12 bg-black rounded border border-transparent transition-colors duration-200 focus:outline-none hover:border-gray-600"
              />
            </div>
            <div>
              <label for="ar-title" class="ml-4"
                >Job title in Arabic <span class="text-gray-400">optional</span></label
              >
              <input
                id="ar-title"
                type="text"
                dir="rtl"
                lang="ar"
                v-model="genInfo.arTitle"
                class="mt-2 px-4 w-full h-12 bg-black rounded border border-transparent transition-colors duration-200 focus:outline-none hover:border-gray-600"
              />
            </div>
          </div>
        </div>
        <div id="design" class="mt-16">
          <h2 class="font-extrabold text-2xl">Card design</h2>
          <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button
              v-for="t in themes"
              :key="t.id"
              type="button"
              :aria-pressed="theme === t.id"
              @click="changeTheme(t.id)"
              class="text-left rounded p-2 bg-gray-800 border-2 transition-colors duration-200 focus:outline-none hover:border-gray-500"
              :class="theme === t.id ? 'border-emerald-500' : 'border-transparent'"
            >
              <span
                class="block h-16 rounded overflow-hidden"
                :style="{ background: t.swatch[0] }"
                aria-hidden="true"
              >
                <span
                  class="block mx-auto mt-3 h-3 w-1/2 rounded"
                  :style="{ background: t.swatch[1] }"
                ></span>
                <span
                  class="block mx-auto mt-2 h-4 w-3/4 rounded-full"
                  :style="{ background: t.swatch[2] }"
                ></span>
              </span>
              <span class="block mt-2 font-extrabold">{{ t.name }}</span>
              <span class="block text-sm text-gray-400 leading-tight">{{ t.note }}</span>
            </button>
          </div>
        </div>
        <div id="step-3" class="mt-16">
          <h2 class="font-extrabold text-2xl">Contact buttons</h2>
          <p class="mt-1 text-gray-400">Phone, email and chat. Shown as large buttons on your card.</p>
          <draggable
            v-model="primaryActions"
            handle=".drag"
            animation="1"
            ghostClass="ghost"
          >
            <transition-group type="transition" name="list">
              <Action
                v-for="(item, index) in primaryActions"
                :key="'item' + index"
                name="primaryActions"
                :type="primaryActions"
                :item="item"
                :index="index"
                :buttonBg="colors.buttonBg.color"
                :removeAction="removeAction"
              />
            </transition-group>
          </draggable>
          <div
            class="mt-6 border-gray-800"
            :class="{ 'border-t pt-6': primaryActions.length }"
          >
            <input
              spellcheck="false"
              type="text"
              v-model="filterPrimary"
              placeholder="Search, e.g. WhatsApp"
              class="
                px-4
                mb-2
                w-full
                h-12
                bg-black
                placeholder-gray-600
                rounded
                border border-transparent
                transition-colors
                duration-200
                focus:outline-none focus:border-gray-600
                hover:border-gray-600
              "
              @keydown.esc="clearFilterActions"
              @keypress.enter="
                filteredAction('filteredPrimaryActions', 'primaryActions')
              "
            />
            <div class="stepC actions">
              <button
                v-for="(action, index) in filteredPrimaryActions"
                :key="index"
                @click="addAction('primaryActions', action.name)"
                class="
                  p-3
                  flex
                  items-center
                  shrink-0
                  rounded
                  hover:bg-gray-600
                  focus:bg-gray-600
                  transition-colors
                  duration-200
                  focus:outline-none
                  bg-gray-700
                "
                :title="
                  action.name.substr(0, 1).toUpperCase() + action.name.slice(1)
                "
                :aria-label="action.name"
              >
                <div
                  class="w-6 h-6 mr-3 shrink-0"
                  v-html="require(`~/assets/icons/${action.icon}.svg?include`)"
                ></div>
                <p class="whitespace-nowrap">
                  {{
                    action.name.substr(0, 1).toUpperCase() +
                    action.name.slice(1)
                  }}
                </p>
              </button>
            </div>
          </div>
        </div>
        <div id="step-4" class="mt-16">
          <h2 class="font-extrabold text-2xl">Social links</h2>
          <p class="mt-1 text-gray-400">LinkedIn and other profiles. Shown as small icons.</p>
          <draggable
            v-model="secondaryActions"
            handle=".drag"
            animation="1"
            ghostClass="ghost"
          >
            <transition-group type="transition" name="list">
              <Action
                v-for="(item, index) in secondaryActions"
                :key="'item' + index"
                name="secondaryActions"
                :type="secondaryActions"
                :item="item"
                :index="index"
                :removeAction="removeAction"
              /> </transition-group
          ></draggable>
          <div
            class="mt-6 border-gray-800"
            :class="{ 'border-t pt-6': secondaryActions.length }"
          >
            <input
              spellcheck="false"
              type="text"
              v-model="filterSecondary"
              placeholder="Search, e.g. WhatsApp"
              class="
                px-4
                mb-2
                w-full
                h-12
                bg-black
                placeholder-gray-600
                rounded
                border border-transparent
                transition-colors
                duration-200
                focus:outline-none focus:border-gray-600
                hover:border-gray-600
              "
              @keydown.esc="clearFilterActions"
              @keypress.enter="
                filteredAction('filteredSecondaryActions', 'secondaryActions')
              "
            />
            <div class="stepC actions">
              <button
                v-for="(action, index) in filteredSecondaryActions"
                :key="index"
                @click="addAction('secondaryActions', action.name)"
                class="
                  p-3
                  flex
                  items-center
                  shrink-0
                  rounded
                  hover:brightness-125
                  focus:brightness-125
                  transition-all
                  duration-200
                  focus:outline-none
                "
                :style="{ background: action.color }"
                :title="
                  action.name.substr(0, 1).toUpperCase() + action.name.slice(1)
                "
              >
                <div
                  class="w-6 h-6 mr-3 shrink-0"
                  v-html="require(`~/assets/icons/${action.icon}.svg?include`)"
                ></div>
                <p
                  class="whitespace-nowrap"
                  :class="{ 'text-gray-900': action.light }"
                >
                  {{
                    action.name.substr(0, 1).toUpperCase() +
                    action.name.slice(1)
                  }}
                </p>
              </button>
            </div>
          </div>
          <!-- class="stepC actions mt-6 border-gray-800"
            :class="{ 'border-t pt-6': secondaryActions.length }" -->
        </div>
        <div id="cover-photo" class="mt-16">
          <h2 class="font-extrabold text-2xl">Cover photo <span class="text-base font-normal text-gray-400">optional</span></h2>
          <div class="stepC">
            <Attachment
              :content="images"
              type="cover"
              :resizeImage="resizeImage"
              label="Add cover photo"
              description="suggested format: svg, jpeg, png or gif"
              :showAlert="showAlert"
            />
            <p class="mt-6 border p-4 rounded border-gray-700 text-gray-400">
              Recommended cover photo size is 960 x 640 pixels, with an aspect
              ratio of 3:2
            </p>
          </div>
        </div>
        <div id="step-5" class="mt-16">
          <h2 class="font-extrabold text-2xl">Extra sections <span class="text-base font-normal text-gray-400">optional</span></h2>
          <div class="stepC">
            <draggable
              v-model="featured"
              handle=".drag"
              animation="1"
              ghostClass="ghost"
            >
              <transition-group type="transition" name="list">
                <Featured
                  v-for="(content, index) in featured"
                  :key="'content' + index"
                  :featured="featured"
                  :resizeImage="resizeImage"
                  :index="index"
                  mimetypes="image/jpeg, image/png, audio/mpeg, video/mp4, video/webm, application/pdf"
                  :showAlert="showAlert"
                /> </transition-group
            ></draggable>

            <div class="flex mt-6">
              <div class="flex flex-wrap items-center">
                <button
                  class="
                    p-3
                    rounded
                    bg-gray-700
                    hover:bg-gray-600
                    focus:bg-gray-600
                    transition-colors
                    duration-200
                    focus:outline-none
                  "
                  @click="addFeature()"
                  aria-label="Add section"
                >
                  <div
                    class="w-6 h-6"
                    v-html="require(`~/assets/icons/add.svg?include`)"
                  ></div>
                </button>
                <p class="ml-3 leading-none">Add section</p>
              </div>
            </div>
            <p class="mt-6 border p-4 rounded border-gray-700 text-gray-400">
              Supported media formats: jpeg, png, mp3, mp4, webm and pdf
            </p>
          </div>
        </div>
        <Download
          :downloadCheckList="downloadCheckList"
          :downloadChecked="downloadChecked"
          :downloadPackage="downloadPackage"
          :publishPackage="publishPackage"
          :hostedURL="hostedURL"
          :card="{
            name: getFullname,
            title: genInfo.title,
            arName: genInfo.arName,
            biz: brand.name,
            logo: brand.logo,
          }"
        />
      </div>
      <div
        id="preview-container"
        class="relative w-full mt-20 sm:mt-0 hidden md:block"
      >
        <div
          id="preview"
          class="
            flex flex-col
            items-center
            justify-center
            sm:sticky sm:top-0
            md:mx-6
            lg:mx-12
          "
        >
          <div id="device" class="bg-black rounded sm:mt-10">
            <h2 class="text-center py-4 font-extrabold text-gray-200">
              LIVE PREVIEW
            </h2>
            <div id="browserFrame" class="overflow-hidden flex flex-col">
              <div
                id="topBar"
                class="
                  topbar
                  border-r-4 border-l-4 border-black
                  bg-gray-900
                  z-10
                "
              >
                <div id="searchField" class="p-2 flex items-center">
                  <input
                    type="text"
                    class="pl-4 h-12 w-full bg-black rounded text-gray-500"
                    aria-label="vCard URL"
                    disabled
                    :value="hostedURL"
                    tabindex="-1"
                  />
                  <div
                    class="w-6 ml-2"
                    v-html="require(`~/assets/icons/ellipsis.svg?include`)"
                  ></div>
                </div>
              </div>
              <Preview
                class="rounded-b-2xl"
                ref="html"
                :username="username"
                :genInfo="genInfo"
                :images="images"
                :featured="featured"
                :colors="colors"
                :primaryActions="primaryActions"
                :secondaryActions="secondaryActions"
                :PreviewMode="PreviewMode"
                :downloadVcard="downloadVcard"
                :footerCredit="footerCredit"
                :showAlert="showAlert"
                :hasLightBG="hasLightBG"
                :downloadKey="downloadKey"
                :pubKeyIsValid="pubKeyIsValid"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    <Vcard ref="vCard" :vCard="vCard" />
    <Footer />
  </div>
</template>

<script>
import Modal from '@/components/Modal'
import Attachment from '@/components/Attachment'
import Action from '@/components/Action'
import Featured from '@/components/Featured'
import Preview from '@/components/Preview'
import Download from '@/components/Download'
import Footer from '@/components/Footer'
import brand from '~/brand.config'
import Cropper from '@/components/Cropper'

import Vcard from '@/components/Vcard'
import JSZip from 'jszip'
import draggable from 'vuedraggable'

import { saveAs } from 'file-saver'
import QRCode from '!!raw-loader!~/static/qrcode.min.js'
// Compiled from the same file the preview uses, so preview and published card match.
import CardCss from '!!raw-loader!sass-loader!~/assets/styles/card.scss'
import { mapState, mapActions } from 'vuex'

export default {
  components: {
    Cropper,
    Modal,
    Attachment,
    Action,
    Featured,
    Preview,
    Download,
    Footer,
    Vcard,
    draggable,
  },

  data() {
    return {
      downloadCheckList: [
        {
          label:
            'I did not attach any link or file that will cause any risk to the user',
          checked: false,
        },
        {
          label: 'I have verified that all the links are working correctly',
          checked: false,
        },
        {
          label: 'I have removed all unused fields and sections',
          checked: false,
        },
      ],
      images: {
        logo: {
          url: null,
          blob: null,
          ext: null,
          mime: null,
          resized: null,
        },
        photo: {
          url: null,
          blob: null,
          ext: null,
          mime: null,
          resized: null,
        },
        cover: {
          url: null,
          blob: null,
          ext: null,
          mime: null,
          resized: null,
        },
      },
      brand,
      origin: window.location.origin,
      editSlug: '',
      editMsg: '',
      editError: false,
      loading: false,
      // Swatch = [page, header/logo area, button] for the picker thumbnails.
      themes: [
        { id: 'classic', name: 'Classic', note: 'Logo band, centred', swatch: [brand.colors.mainBg, brand.colors.logoBg, brand.colors.buttonBg] },
        { id: 'executive', name: 'Executive', note: 'Dark navy', swatch: [`color-mix(in srgb, ${brand.colors.buttonBg} 45%, #050b18)`, '#ffffff', '#ffffff'] },
        { id: 'minimal', name: 'Minimal', note: 'White, contact list', swatch: ['#ffffff', '#e6ebf3', brand.colors.buttonBg] },
        { id: 'spotlight', name: 'Spotlight', note: 'Floating card', swatch: [brand.colors.buttonBg, '#ffffff', '#ffffff'] },
      ],
      colors: Object.fromEntries(
        Object.entries(brand.colors).map(([k, color]) => [
          k,
          { color, openPalette: false },
        ])
      ),
      genInfo: {
        fname: null,
        lname: null,
        pronouns: null,
        arName: null,
        arTitle: null,
        title: null,
        biz: brand.name,
        addr: brand.address,
        desc: brand.description,
        key: null,
        tracker: null,
        fontLink: null,
        fontCss: null,
      },
      primaryActions: [],
      filterPrimary: '',
      secondaryActions: [],
      filterSecondary: '',
      actions: {
        primaryActions: [
          {
            name: 'Mobile',
            icon: 'call',
            href: 'tel:',
            placeholder: '+XX XXXXX XXXXX',
            value: null,
            label: 'Mobile number',
            order: 0,
            isURL: 0,
          },
          {
            name: 'Office',
            icon: 'call',
            href: 'tel:',
            placeholder: '+XX XXXXX XXXXX',
            value: null,
            label: 'Office number',
            order: 1,
            isURL: 0,
          },
          {
            name: 'Home',
            icon: 'call',
            href: 'tel:',
            placeholder: '+XX XXXXX XXXXX',
            value: null,
            label: 'Home number',
            order: 2,
            isURL: 0,
          },
          {
            name: 'SMS',
            icon: 'sms',
            href: 'sms:',
            placeholder: '+XX XXXXX XXXXX',
            value: null,
            label: 'SMS mobile number',
            order: 3,
            isURL: 0,
          },
          {
            name: 'Email',
            icon: 'email',
            href: 'mailto:',
            placeholder: 'info@example.com',
            value: null,
            label: 'Email address',
            order: 4,
          },
          {
            name: 'Website',
            icon: 'website',
            placeholder: 'https://example.com',
            value: null,
            label: 'Website URL',
            order: 5,
            isURL: 1,
          },
          {
            name: 'Store',
            icon: 'store',
            placeholder: 'https://example.com/storeID',
            value: null,
            label: 'Online Store URL',
            order: 6,
            isURL: 1,
          },
          {
            name: 'Location',
            icon: 'location',
            placeholder: 'https://osm.org/go/location',
            value: null,
            label: 'Map location URL',
            order: 7,
            isURL: 1,
          },

          {
            name: 'Signal',
            icon: 'signal',
            href: 'https://signal.me/#p/',
            placeholder: '+XXXXXXXXXXXX',
            value: null,
            label: 'Signal number with country code (no spaces)',
            order: 8,
            isURL: 1,
          },
          {
            name: 'Telegram',
            icon: 'telegram',
            href: 'https://t.me/',
            placeholder: 'username',
            value: null,
            label: 'Telegram username',
            order: 9,
            isURL: 1,
          },
          {
            name: 'Matrix',
            icon: 'matrix',
            href: 'https://matrix.to/#/',
            placeholder: '@username:matrix.org',
            value: null,
            label: 'Matrix userID',
            order: 10,
            isURL: 1,
          },
          {
            name: 'WhatsApp',
            icon: 'whatsapp',
            placeholder: 'https://wa.me/profileID',
            value: null,
            label: 'WhatsApp profile URL',
            order: 11,
            isURL: 1,
          },
          {
            name: 'Messenger',
            icon: 'messenger',
            href: 'https://m.me/',
            placeholder: 'username',
            value: null,
            label: 'Messenger username',
            order: 12,
            isURL: 1,
          },
          {
            name: 'Skype',
            icon: 'skype',
            href: 'skype:',
            hrefEnd: '?chat',
            placeholder: 'username',
            value: null,
            label: 'Skype username',
            order: 13,
            isURL: 1,
          },
          {
            name: 'Line',
            icon: 'line',
            href: 'https://line.me/ti/p/',
            placeholder: 'LINE ID',
            value: null,
            label: 'Line profile ID',
            order: 14,
            isURL: 1,
          },
          {
            name: 'Viber',
            icon: 'viber',
            href: 'viber://chat?number=',
            placeholder: 'XX XXXXX XXXXX',
            value: null,
            label: 'Viber mobile number',
            order: 15,
            isURL: 1,
          },
          {
            name: 'WeChat',
            icon: 'wechat',
            href: 'weixin://dl/chat?',
            placeholder: 'WeChat ID',
            value: null,
            label: 'WeChat profile ID',
            order: 16,
            isURL: 1,
          },
          {
            name: 'Calendar',
            icon: 'calendar',
            placeholder: 'https://example.com/calendarID',
            value: null,
            label: 'Calendar URL',
            order: 17,
            isURL: 1,
          },
          {
            name: 'XMPP',
            icon: 'xmpp',
            href: 'xmpp:',
            placeholder: 'XMPP ID',
            value: null,
            label: 'XMPP ID',
            order: 18,
            isURL: 1,
          },
          // {
          //   name: 'IRC',
          //   icon: 'irc',
          //   href: 'irc:',
          //   placeholder: 'IRC ID',
          //   value: null,
          //   label: 'IRC ID',
          //   order: 19,
          //   isURL: 1,
          // },
        ],
        secondaryActions: [
          // todo: Fix Instagram gradient icon preview
          {
            name: 'Instagram',
            icon: 'instagram',
            href: 'https://instagram.com/',
            placeholder: 'username',
            value: null,
            color: '#ffffff',
            light: 1,
            gradientIcon: 1,
            label: 'Instagram username',
          },
          {
            name: 'Threads',
            icon: 'threads',
            href: 'https://www.threads.net/',
            placeholder: '@username',
            value: null,
            color: '#000000',
            label: 'Threads username',
          },
          {
            name: 'Pixelfed',
            icon: 'pixelfed',
            placeholder: 'https://pixelfed.social/username',
            value: null,
            color: '#8d59a8',
            label: 'Pixelfed profile URL',
          },
          {
            name: 'Facebook',
            icon: 'facebook',
            href: 'https://facebook.com/',
            placeholder: 'username or pagename',
            value: null,
            color: '#1877f2',
            label: 'Facebook username or pagename',
          },
          {
            name: 'Diaspora',
            icon: 'diaspora',
            placeholder: 'https://diaspora.social/username',
            value: null,
            color: '#000000',
            label: 'Diaspora profile URL',
          },
          {
            name: 'Friendica',
            icon: 'friendica',
            placeholder: 'https://friendica.social/username',
            value: null,
            color: '#1d6e9a',
            label: 'Friendica profile URL',
          },
          {
            name: 'Twitter',
            icon: 'twitter',
            href: 'https://twitter.com/',
            placeholder: 'username',
            value: null,
            color: '#1da1f2',
            label: 'Twitter username',
          },
          {
            name: 'Mastodon',
            icon: 'mastodon',
            placeholder: 'https://mastodon.social/@username',
            value: null,
            color: '#2b90d9',
            label: 'Mastodon profile URL',
          },
          {
            name: 'LinkedIn',
            icon: 'linkedin',
            href: 'https://linkedin.com/',
            placeholder: 'in/username or company/companyname',
            value: null,
            color: '#0077b5',
            label: 'Linkedin username or companyname',
          },
          {
            name: 'YouTube',
            icon: 'youtube',
            href: 'https://youtube.com/',
            placeholder: 'channel name or ID',
            value: null,
            color: '#ff0000',
            label: 'Youtube channel name or ID',
          },
          {
            name: 'Vimeo',
            icon: 'vimeo',
            href: 'https://vimeo.com/',
            placeholder: 'channelname',
            value: null,
            color: '#1ab7ea',
            label: 'Vimeo channelname',
          },
          {
            name: 'Peertube',
            icon: 'peertube',
            placeholder: 'https://peertube.video/channelname',
            value: null,
            color: '#ffffff',
            light: 1,
            label: 'Peertube channel URL',
          },
          {
            name: 'Pinterest',
            icon: 'pinterest',
            href: 'https://pinterest.com/',
            placeholder: 'username',
            value: null,
            color: '#bd081c',
            label: 'Pinterest username',
          },
          {
            name: 'Behance',
            icon: 'behance',
            href: 'https://behance.net/',
            placeholder: 'username',
            value: null,
            color: '#1769ff',
            label: 'Behance username',
          },
          {
            name: 'Dribbble',
            icon: 'dribbble',
            href: 'https://dribbble.com/',
            placeholder: 'username',
            value: null,
            color: '#ea4c89',
            label: 'Dribbble username',
          },
          {
            name: 'Reddit',
            icon: 'reddit',
            href: 'https://reddit.com/',
            placeholder: 'username',
            value: null,
            color: '#ff5700',
            label: 'Reddit username',
          },
          {
            name: 'VK',
            icon: 'vk',
            href: 'https://vk.com/',
            placeholder: 'pagename',
            value: null,
            color: '#4a76a8',
            label: 'VK page URL',
          },
          {
            name: 'Snapchat',
            icon: 'snapchat',
            href: 'https://www.snapchat.com/add/',
            placeholder: 'username',
            value: null,
            color: '#fffc00',
            light: 1,
            label: 'Snapchat username',
          },
          {
            name: 'Tumblr',
            icon: 'tumblr',
            href: 'https://',
            hrefEnd: '.tumblr.com/',
            placeholder: 'username',
            value: null,
            color: '#2c4762',
            label: 'Tumblr blog URL',
          },
          {
            name: 'Quora',
            icon: 'quora',
            href: 'https://quora.com/',
            placeholder: 'username',
            value: null,
            color: '#a82400',
            label: 'Quora username',
          },
          {
            name: 'Medium',
            icon: 'medium',
            placeholder: 'https://medium.com/publication_name',
            value: null,
            color: '#000000',
            label: 'Medium publication',
          },
          {
            name: 'Discord',
            icon: 'discord',
            placeholder: 'https://discord.gg/invitecode',
            value: null,
            color: '#7289da',
            label: 'Discord channel invite link',
          },
          {
            name: 'Twitch',
            icon: 'twitch',
            href: 'https://twitch.tv/',
            placeholder: 'username',
            value: null,
            color: '#9146ff',
            label: 'Twitch username',
          },
          {
            name: 'Spotify',
            icon: 'spotify',
            href: 'https://open.spotify.com/user/',
            placeholder: 'username',
            value: null,
            color: '#1ed760',
            label: 'Spotify username',
          },
          {
            name: 'Soundcloud',
            icon: 'soundcloud',
            href: 'https://soundcloud.com/',
            placeholder: 'username',
            value: null,
            color: '#ff3300',
            label: 'Soundcloud username',
          },
          {
            name: 'Funkwhale',
            icon: 'funkwhale',
            placeholder: 'https://funkwhale.audio/username',
            value: null,
            color: '#ffffff',
            light: 1,
            label: 'Funkwhale username',
          },
          {
            name: 'GitHub',
            icon: 'github',
            href: 'https://github.com/',
            placeholder: 'username',
            value: null,
            color: '#333',
            label: 'Github username',
          },
          {
            name: 'GitLab',
            icon: 'gitlab',
            href: 'https://gitlab.com/',
            placeholder: 'username',
            value: null,
            color: '#171321 ',
            label: 'Gitlab username',
          },
          {
            name: 'Codeberg',
            icon: 'codeberg',
            href: 'https://codeberg.org/',
            placeholder: 'username',
            value: null,
            color: '#2185d0',
            label: 'Codeberg username',
          },
          {
            name: 'Yelp',
            icon: 'yelp',
            href: 'https://yelp.com/',
            placeholder: 'bizname',
            value: null,
            color: '#fff',
            light: 1,
            label: 'Yelp pagename',
          },
          {
            name: 'PayPal',
            icon: 'paypal',
            href: 'https://paypal.me/',
            placeholder: 'username',
            value: null,
            color: '#003087',
            label: 'PayPal.me URL',
          },
          {
            name: 'Patreon',
            icon: 'patreon',
            href: 'https://patreon.com/',
            placeholder: 'username',
            value: null,
            color: '#FF424D',
            label: 'Patreon URL',
          },
          {
            name: 'Open-Collective',
            icon: 'open-collective',
            href: 'https://opencollective.com/',
            placeholder: 'projectname',
            value: null,
            color: '#fff',
            light: 1,
            label: 'Open Collective projectname',
          },
          {
            name: 'TikTok',
            icon: 'tiktok',
            href: 'https://tiktok.com/',
            placeholder: 'username',
            value: null,
            color: '#fff',
            light: 1,
            label: 'TikTok username',
          },
          {
            name: 'Cash App',
            icon: 'cashapp',
            href: 'https://cash.app/',
            placeholder: '$username',
            value: null,
            color: '#fff',
            light: 1,
            label: 'Cash App username',
          },
          {
            name: 'Siilo',
            icon: 'siilo',
            href: 'https://app.siilo.com/qr/',
            placeholder: 'userID',
            value: null,
            color: '#17233b',
            label: 'Siilo userID',
          },
          {
            name: 'App Store',
            icon: 'appstore',
            placeholder: 'https://apps.apple.com/in/app/appname/id',
            value: null,
            color: 'linear-gradient(#5fc9f8, #147efb)',
            label: 'App Store developer/app URL',
          },
          {
            name: 'Play Store',
            icon: 'playstore',
            placeholder: 'https://play.google.com/store/apps/details?id=',
            value: null,
            color: '#fff',
            light: 1,
            label: 'Play Store developer/app URL',
          },
          {
            name: 'ArtStation',
            icon: 'artstation',
            href: 'https://www.artstation.com/',
            placeholder: 'username',
            value: null,
            color: '#171717',
            label: 'ArtStation username',
          },
          {
            name: 'Buy me a coffee',
            icon: 'buymeacoffee',
            href: 'https://www.buymeacoffee.com/',
            placeholder: 'username',
            value: null,
            color: '#ffdd00',
            light: 1,
            label: 'Buy me a coffee username',
          },
        ],
      },
      featured: [
        {
          title: 'Section title',
          content: [],
        },
      ],
      footerCredit: false,
      PreviewMode: true,
      content: null,
      inView: false,
      showPreview: false,
      scrollPos: null,
      opening: false,
    }
  },
  computed: {
    ...mapState(['theme']),
    getFullname() {
      let fn = this.genInfo.fname
      let ln = this.genInfo.lname
      return (fn + ln).length ? `${fn ? fn : ''}${ln ? ' ' + ln : ''}` : null
    },
    pubKeyIsValid() {
      if (this.genInfo.key) {
        if (!this.genInfo.key.match(/^(-----BEGIN PGP PUBLIC KEY BLOCK-----)/))
          return false

        if (!this.genInfo.key.match(/(-----END PGP PUBLIC KEY BLOCK-----)$/))
          return false

        return true
      } else return false
    },
    downloadChecked() {
      return this.downloadCheckList.filter((e) => e.checked).length == 3
    },
    hostedURL() {
      return `${window.location.origin}/${this.username}/`
    },
    username() {
      return this.getFullname
        ? this.getFullname.toLowerCase().replace(/\W+/g, '')
        : 'username'
    },
    orderedPrimaryActions() {
      return this.actions.primaryActions.sort((a, b) =>
        a.order > b.order ? 1 : a.order < b.order ? -1 : 0
      )
    },
    filteredPrimaryActions() {
      return this.orderedPrimaryActions.filter((e) =>
        e.name.toLowerCase().includes(this.filterPrimary.toLowerCase())
      )
    },
    orderedSecondaryActions() {
      return this.actions.secondaryActions.sort((a, b) =>
        a.name.localeCompare(b.name)
      )
    },
    filteredSecondaryActions() {
      return this.orderedSecondaryActions.filter((e) =>
        e.name.toLowerCase().includes(this.filterSecondary.toLowerCase())
      )
    },
    vCard() {
      const getNumber = (type) => {
        let no = this.primaryActions
          .map((e) => (e.name == type ? e.value : null))
          .filter((e) => e)[0]
        return no ? no.replace(/\s/g, '') : null
      }
      let email = this.primaryActions
        .map((e) => (e.name == 'Email' ? e.value : null))
        .filter((e) => e)[0]
      let website = this.primaryActions
        .map((e) => (e.name == 'Website' ? e.value : null))
        .filter((e) => e)[0]
      let actions = [
        ...this.primaryActions,
        ...this.secondaryActions.map((e) => {
          return { ...e, isURL: 1 }
        }),
      ]
      let urls = actions
        .map((e) => {
          if (e.isURL && e.value) {
            return {
              title: e.name,
              url:
                (e.href ? e.href : '') + e.value + (e.hrefEnd ? e.hrefEnd : ''),
            }
          }
          return false
        })
        .filter((e) => e)

      let note = this.genInfo.desc
        ? this.genInfo.desc.replace(/[\r\n]+/gm, '')
        : null
      let key = this.pubKeyIsValid ? window.btoa(this.genInfo.key) : null
      let randomNumber = Math.floor(100000000 + Math.random() * 900000)
      return {
        fn: this.genInfo.fname,
        ln: this.genInfo.lname,
        title: this.genInfo.title,
        org: this.genInfo.biz,
        addr: this.genInfo.addr,
        cell: getNumber('Mobile'),
        work: getNumber('Office'),
        home: getNumber('Home'),
        sms: getNumber('SMS'),
        email,
        hostedURL: this.hostedURL,
        website,
        urls,
        key,
        note,
        uid: `card-${randomNumber}`,
      }
    },
  },
  methods: {
    ...mapActions(['changeTheme']),
    togglePreview() {
      this.opening = true
      let c = this.$refs.container
      if (this.showPreview) {
        c.classList.remove('overflow-y-hidden', 'h-screen')
        window.scrollTo(0, this.scrollPos)
        this.opening = false
      } else {
        this.scrollPos = window.scrollY
        setTimeout(() => {
          c.classList.add('overflow-y-hidden', 'h-screen')
          this.opening = false
        }, 400)
      }
      this.showPreview = !this.showPreview
    },
    checkView() {
      let e = this.$refs.create
      if (e) {
        let top = e.getBoundingClientRect().top
        this.inView = this.showPreview ? true : top < 0
      }
    },
    clearContent() {
      this.content = null
    },
    create() {
      this.$refs.create.scrollIntoView({ behavior: 'smooth' })
    },
    getTitle(e) {
      return e.toLowerCase().split(' ').join('_')
    },
    addFeature() {
      this.featured.push({
        title: 'Section title',
        content: [],
      })
    },
    hasLightBG(e) {
      let hex = this.colors[e].color
      hex = hex.slice(1)
      if (hex.length === 3) {
        hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]
      }
      let r = parseInt(hex.slice(0, 2), 16)
      let g = parseInt(hex.slice(2, 4), 16)
      let b = parseInt(hex.slice(4, 6), 16)
      const brightness = Math.round(
        (parseInt(r) * 299 + parseInt(g) * 587 + parseInt(b) * 114) / 1000
      )
      return brightness > 125 ? true : false
    },
    showAlert(content) {
      this.content = content
    },
    clearFilterActions() {
      this.filterPrimary = this.filterSecondary = ''
    },
    filteredAction(filterType, actionType) {
      if (this[filterType].length)
        this.addAction(actionType, this[filterType][0].name)
      this.clearFilterActions()
    },
    addAction(type, name) {
      let index = this.actions[type].findIndex((e) => e.name === name)
      this[type].push(this.actions[type][index])
      this.actions[type].splice(index, 1)
      this.clearFilterActions()
    },
    removeAction(type, index) {
      this.actions[type].unshift(this[type][index])
      this[type].splice(index, 1)
    },
    downloadVcard() {
      let blob = new Blob([this.$refs.vCard.$refs.vCard.innerText], {
        type: 'text/plain',
      })
      saveAs(window.URL.createObjectURL(blob), `${this.username}.vcf`)
    },
    downloadKey() {
      let blob = new Blob([this.genInfo.key], {
        type: 'text/plain',
      })
      saveAs(
        window.URL.createObjectURL(blob),
        `${this.getFullname}'s public key.asc`
      )
    },
    async resizeImage(type, mime, index1, index2) {
      let vm = this
      let reader = new FileReader()
      let file
      if (index2 >= 0) {
        if (type == 'image') {
          file = await this.featured[index1].content[index2].file
        } else if (type == 'music') {
          file = await this.featured[index1].content[index2].cover
        } else if (type == 'product') {
          file = await this.featured[index1].content[index2].image.file
        }
      } else {
        file = await this.images[type].blob
      }
      let canvas = document.createElement('canvas')
      let ctx = canvas.getContext('2d')
      let img = document.createElement('img')
      let maxWidth, maxHeight
      reader.onload = (e) => {
        img.src = e.target.result
        img.onload = () => {
          if (type == 'photo') {
            canvas.width = canvas.height = 320
          } else {
            if (type == 'logo') {
              maxWidth = 960
              maxHeight = 192
            } else {
              maxWidth = maxHeight = 960
            }
            let width = img.width
            let height = img.height

            if (width > maxWidth) {
              height *= maxWidth / width
              width = maxWidth
            }
            if (height > maxHeight) {
              width *= maxHeight / height
              height = maxHeight
            }
            canvas.width = width
            canvas.height = height
          }
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height)
          canvas.toBlob(
            (blob) => {
              let image = new File([blob], type, {
                type: mime,
              })
              if (index2 >= 0) {
                if (type == 'image') {
                  vm.featured[index1].content[index2].file = image
                } else if (type == 'music') {
                  vm.featured[index1].content[index2].cover = image
                } else if (type == 'product') {
                  vm.featured[index1].content[index2].image.file = image
                }
              } else {
                vm.images[type].resized = image
              }
            },
            mime,
            0.8
          )
        }
      }
      reader.readAsDataURL(file)
    },
    getTrackingCode() {
      let regex = /<script[^<]*<\/script>/g
      let tracker = this.genInfo.tracker
      if (regex.test(tracker)) {
        let scripts = tracker.match(regex)
        let temp = document.createElement('div')
        temp.innerHTML = tracker
        return scripts.length && temp
      }
      return false
    },
    // Builds the card zip; resolves once the preview is back in preview mode.
    buildPackage() {
      this.PreviewMode = false
      return new Promise((resolve) => {
        setTimeout(() => {
          let el = new DOMParser().parseFromString(
            this.$refs.html.$refs.html.outerHTML,
            'text/html'
          )

          // Inject stylesheets
          let styleLink = document.createElement('link')
          styleLink.rel = 'stylesheet'
          styleLink.href = './style.min.css'
          el.querySelector('head').appendChild(styleLink)

          // Inject qrcode script
          let qrcode = document.createElement('script')
          qrcode.src = './qrcode.min.js'
          el.querySelector('body').appendChild(qrcode)

          // Inject general script
          let modals = document.createElement('script')
          modals.innerText =
            'let m=document.getElementById("modal"),c=document.getElementById("close"),ki=document.getElementById("keyView"),cv=document.getElementById("copyView"),curl=document.getElementById("copyURL"),qrv=document.getElementById("qrView"),qr=document.getElementById("qr"),s=document.getElementById("share"),sqr=document.getElementById("showQR"),sk=document.getElementById("showKey");function tC(e){"2rem"==e.style.top?(e.style.visibility="visible",e.style.top="0px",e.style.opacity=1):(e.style.top="2rem",e.style.opacity=0,setTimeout(()=>{e.style.visibility="hidden"},200))}function dN(e){e.style.display="none"}window.addEventListener("load",()=>{document.querySelector("#topActions").style.display="flex",qr.innerHTML=new QRCode({content:window.location.href,container:"svg-viewbox",join:!0,ecl:"L",padding:0}).svg()}),navigator.canShare?s.addEventListener("click",()=>{navigator.share({title:document.title,text:"You can view my Digital Business Card here:",url:window.location.href})}):s.addEventListener("click",()=>{tC(m),cv.style.display="flex",dN(qrv),ki&&dN(ki)}),sqr.addEventListener("click",()=>{tC(m),qrv.style.display="block",dN(cv),ki&&dN(ki)}),sk&&sk.addEventListener("click",()=>{tC(m),ki&&(ki.style.display="flex"),dN(cv),dN(qrv)}),c.addEventListener("click",()=>tC(m)),curl.addEventListener("click",async()=>{let e=curl.querySelectorAll(".iconColor")[1];await navigator.clipboard.writeText(window.location.href).then(t=>{e.innerText="Copied",setTimeout(()=>{e.innerText="Copy URL"},1e3)})});'
          el.querySelector('body').appendChild(modals)

          // Inject media script
          let mediaHandler = document.createElement('script')
          mediaHandler.innerText =
            'let pC=document.querySelectorAll(".pCtrl"),pP=document.querySelectorAll(".playPause"),srcs=document.querySelectorAll(".source");srcs.forEach(e=>{e.style.pointerEvents="none",e.removeAttribute("controls")}),pC.forEach((e,l)=>{e.style.display="flex";let r=e.querySelector(".currentTime"),s=e.querySelector(".seekBar"),t=e.querySelector(".playPause"),a=t.querySelector(".play"),c=t.querySelector(".pause");srcs[l].addEventListener("timeupdate",()=>{let e=srcs[l].currentTime,t=100/srcs[l].duration*e;s.value=t,100==t&&(s.value=0,a.style.display="block",c.style.display="none");let o=Math.floor(e/60),y=Math.floor(e%60);o.toString().length<2&&(o="0"+o),y.toString().length<2&&(y="0"+y),r.value=o+":"+y}),s.addEventListener("change",()=>{srcs[l].currentTime=srcs[l].duration*(parseInt(s.value)/100)}),t.addEventListener("click",()=>{srcs[l].paused?(srcs.forEach((e,r)=>{l!=r&&(e.paused||e.pause())}),pP.forEach((e,l)=>{let r=e.querySelector(".play"),s=e.querySelector(".pause");r.style.display="block",s.style.display="none"}),srcs[l].play(),a.style.display="none",c.style.display="block"):(srcs[l].pause(),c.style.display="none",a.style.display="block")})});'
          if (this.featured.length)
            el.querySelector('body').appendChild(mediaHandler)

          // Inject tracking scripts
          let tracker = this.getTrackingCode()
          while (tracker.firstChild) el.head.appendChild(tracker.firstChild)

          // Create blobs
          let html = new Blob(
            [`<!DOCTYPE html>${el.documentElement.outerHTML}`],
            {
              type: 'text/html',
            }
          )
          let css = new Blob([CardCss], {
            type: 'text/css',
          })
          let vCard = new Blob([this.$refs.vCard.$refs.vCard.innerText], {
            type: 'text/plain',
          })
          let qrScript = new Blob([QRCode], {
            type: 'application/javascript',
          })

          // Prepare files
          let username = this.username
          let zip = new JSZip()
          zip.folder(username).file('index.html', html)
          zip.folder(username).file('style.min.css', css)
          zip.folder(username).file('qrcode.min.js', qrScript)

          // Image attachments
          for (const key in this.images) {
            if (this.images[key].url) {
              zip
                .folder(username)
                .file(
                  `${key}.${this.images[key].ext}`,
                  this.images[key].resized
                )
            }
          }

          // Featured content
          let hasFeaturedContent = this.featured.filter(
            (e) => e.content.length
          ).length
          if (hasFeaturedContent) {
            this.featured.forEach((item) => {
              item.content.forEach((item) => {
                if (item.contentType == 'media') {
                  zip
                    .folder(username)
                    .folder('media')
                    .file(`${this.getTitle(item.title)}.${item.ext}`, item.file)
                  if (item.type.match(/music|document/gi)) {
                    if (!item.info) {
                      zip
                        .folder(username)
                        .folder('media')
                        .file(
                          `${this.getTitle(item.title)}.${item.coverExt}`,
                          item.cover
                        )
                    }
                  }
                } else if (item.contentType == 'product' && item.image) {
                  zip
                    .folder(username)
                    .folder('media')
                    .file(
                      `${this.getTitle(item.image.title)}.${item.image.ext}`,
                      item.image.file
                    )
                }
              })
            })
          }

          //  Public key
          let name = this.getFullname
          if (this.pubKeyIsValid) {
            zip
              .folder(username)
              .file(`${name}'s public key.asc`, this.genInfo.key)
          }

          // VCARD
          zip.folder(username).file(`${username}.vcf`, vCard)

          // Editable data, so "Already have a card?" can load it back later.
          const pick = (o, keys) =>
            keys.reduce((r, k) => ((r[k] = o[k] || null), r), {})
          const imageFile = (k) =>
            this.images[k].url ? `${k}.${this.images[k].ext}` : null
          zip.folder(username).file(
            'card.json',
            JSON.stringify({
              version: 1,
              genInfo: pick(this.genInfo, [
                'fname',
                'lname',
                'pronouns',
                'title',
                'arName',
                'arTitle',
              ]),
              primaryActions: this.primaryActions.map(({ name, value }) => ({ name, value })),
              secondaryActions: this.secondaryActions.map(({ name, value }) => ({ name, value })),
              theme: this.theme,
              images: { photo: imageFile('photo'), cover: imageFile('cover') },
            })
          )

          this.PreviewMode = true
          resolve({ zip, username, name })
        }, 250)
      })
    },
    async downloadPackage() {
      if (!this.downloadChecked) return
      const { zip, name } = await this.buildPackage()
      const blob = await zip.generateAsync({ type: 'blob' })
      saveAs(blob, `${name}'s Digital Business Card.zip`)
    },
    // Sends just the card folder to publish.php, which unpacks it at /<username>/.
    async publishPackage() {
      if (!this.getFullname) throw new Error('Enter your name first.')
      const { zip, username } = await this.buildPackage()
      const form = new FormData()
      form.append('slug', username)
      form.append(
        'card',
        await zip.folder(username).generateAsync({ type: 'blob' }),
        'card.zip'
      )
      const res = await fetch('publish.php', { method: 'POST', body: form })
      const text = await res.text()
      if (!res.ok) throw new Error(text || `Publish failed (${res.status})`)
      // A server without PHP returns the script source with 200; don't call that success.
      if (!text.startsWith('Published'))
        throw new Error('Publishing is not available here: publish.php did not run.')
      return text
    },
    async loadImage(key, url) {
      const res = await fetch(url, { cache: 'no-store' })
      if (!res.ok) throw new Error(`Could not load ${url}`)
      const blob = await res.blob()
      const ext = url.split('.').pop().toLowerCase()
      const mime = blob.type || (ext === 'svg' ? 'image/svg+xml' : `image/${ext}`)
      const file = new File([blob], key, { type: mime })
      this.images[key] = { url: URL.createObjectURL(file), blob: file, ext, mime, resized: file }
    },
    // Loads a published card's card.json (and its photos) back into the form.
    async loadCard() {
      const slug = this.editSlug
        .trim()
        .toLowerCase()
        .replace(/\/+$/, '')
        .replace(/^.*\//, '')
        .replace(/\W+/g, '')
      if (!slug || this.loading) return
      this.loading = true
      this.editError = false
      this.editMsg = ''
      try {
        const res = await fetch(`/${slug}/card.json`, { cache: 'no-store' })
        const data = res.ok ? await res.json().catch(() => null) : null
        if (!data || !data.genInfo)
          throw new Error(
            `No editable card found at /${slug}/. Cards published before this update need filling in once more.`
          )
        Object.assign(this.genInfo, data.genInfo)
        for (const type of ['primaryActions', 'secondaryActions']) {
          while (this[type].length) this.removeAction(type, 0)
          for (const a of data[type] || []) {
            if (!this.actions[type].some((e) => e.name === a.name)) continue
            this.addAction(type, a.name)
            this[type][this[type].length - 1].value = a.value
          }
        }
        if (data.theme) this.changeTheme(data.theme)
        const images = data.images || {}
        for (const key of ['photo', 'cover']) {
          if (images[key] && /^[a-z]+\.\w+$/.test(images[key]))
            await this.loadImage(key, `/${slug}/${images[key]}`)
        }
        this.editMsg = `Loaded ${slug}. Make your changes, then publish again.`
      } catch (e) {
        this.editError = true
        this.editMsg = e.message
      } finally {
        this.loading = false
      }
    },
  },
  mounted() {
    this.changeTheme(brand.theme)
    // Logo keeps its static URL so the exported card references logo.<ext>.
    this.loadImage('logo', brand.logo).then(() => {
      this.images.logo.url = brand.logo
    })
    if (brand.website) {
      this.addAction('primaryActions', 'Website')
      this.primaryActions[0].value = brand.website
    }
    window.addEventListener('scroll', this.checkView)
    // window.onbeforeunload = function () {
    //   return 'Your work will be lost.'
    // }
  },
}
</script>
