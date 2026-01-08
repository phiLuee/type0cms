import { Node, mergeAttributes } from '@tiptap/core'

export const MediaNode = Node.create({
  name: 'media',

  group: 'block',

  atom: true,

  addAttributes() {
    return {
      src: {
        default: null,
      },
      alt: {
        default: null,
      },
      title: {
        default: null,
      },
      mediaId: {
        default: null,
      },
      mediaType: {
        default: 'image',
      },
    }
  },

  parseHTML() {
    return [
      {
        tag: 'div[data-media-id]',
      },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    if (HTMLAttributes.mediaType === 'image') {
      return ['img', mergeAttributes(HTMLAttributes)]
    }
    
    if (HTMLAttributes.mediaType === 'video') {
      return ['video', mergeAttributes({ controls: true }, HTMLAttributes), ['source', { src: HTMLAttributes.src }]]
    }

    return ['div', mergeAttributes(HTMLAttributes), ['a', { href: HTMLAttributes.src }, HTMLAttributes.title || 'Download']]
  },

  addCommands() {
    return {
      setMedia: (options) => ({ commands }) => {
        return commands.insertContent({
          type: this.name,
          attrs: options,
        })
      },
    }
  },
})
