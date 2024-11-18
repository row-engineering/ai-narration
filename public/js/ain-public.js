(function() {
	class AINPlayer {
		/**************
		 * INITIALIZE *
		 **************/
		constructor(ainData) {
			this.data          = ainData
			this.files         = this.data.audio.tracks
			this.durations     = this.data.audio.duration
			this.loadIdx       = 0      // which audio file are we loading?
			this.playIdx       = 0      // which audio file are we playing?
			this.bufferedTime  = [0]    // tracking buffered time for all files
			this.playedTime    = [0]    // tracking played time for all files
			this.totalPlay     = 0      // combining played time for all files
			this.totalDuration = this.durations.reduce((total,num) => total + num, 0)

			this.insertPlayer()
			this.saveSelectors()

			this.active 	= false
			this.playing 	= false
			this.muted 		= false
			this.playRate	= 1
			this.volume		= 100
			this.seek.max = Math.floor(this.totalDuration)

			this.setContainerHeight()
			this.initSequentialLoading()
			this.addPlaybackEventListeners()
			this.mediaSessionSetup()
			this.setAudioMilestones()
		}

		insertPlayer() {
			const firstGraf = document.querySelector('.post-content > p:first-child')
			if (firstGraf) {
				firstGraf.insertAdjacentHTML('afterend', this.playerMarkup(true))
			}
		}

		playerMarkup() {
			let learnMoreLink = ''
			if (this.data.config.learnMoreLink) {
				learnMoreLink = `
					<a class="ain-player__about" href="${this.data.config.learnMoreLink}" target="_blank">Learn more</a>
				`
			}
			return `
				<div class="ain__container">
					<div class="ain">
						<div class="ain-player" data-play="pause" data-volume="on" data-active="false">
							<audio id="ain-track" src="${this.files[0]}" preload="metadata"></audio>
							<div class="ain-player__intro">
								<button class="ain-player__cta">
									<div class="ain-player__cta__icon ain-player__icon ain-player__icon--large">
										<svg id="play-icon" class="play-icon"><use xlink:href="#ain-play"></use></svg>
									</div>
									<div class="ain-player__cta__msg">
										<span class="ain-player__cta__msg-text">Listen to story</span>
										<span class="ain-player__cta__msg-length meta">${this.calculateMinutes()}</span>
									</div>
								</button>
								${learnMoreLink}
							</div>
							<div class="ain-player__controls">
								<button class="ain-player__play ain-player__icon ain-player__icon--large" aria-label="Play/pause this episode">
									<svg id="play-icon" class="play-icon"><use xlink:href="#ain-play"></use></svg>
									<svg id="pause-icon"><use xlink:href="#ain-pause"></use></svg>
								</button>
								<button class="ain-player__skip ain-player__skip--bwd ain-player__icon ain-player__icon--small" aria-label="skip backward">
									<svg><use xlink:href="#ain-skip-backward"></use></svg>
								</button>
								<button class="ain-player__skip ain-player__skip--fwd ain-player__icon ain-player__icon--small" aria-label="skip forward">
									<svg><use xlink:href="#ain-skip-forward"></use></svg>
								</button>
								<div class="ain-player__volume">
									<div class="ain-player__volume__range">
										<input class="ain-player__volume__range-input" type="range" max="100" value="100" aria-label="adjust volume">
									</div>
									<button class="ain-player__volume__button ain-player__icon ain-player__icon--small" aria-label="mute/unmute">
										<svg id="unmuted-icon"><use xlink:href="#ain-unmuted"></use></svg>
										<svg id="muted-icon"><use xlink:href="#ain-muted"></use></svg>
									</button>
								</div>
								<input class="ain-player__seek" type="range" max="100" value="0" aria-label="seek">
								<div class="ain-player__time meta">
									<span class="ain-player__time-played">0:00</span><span class="ain-player__time-slash">/</span><span class="ain-player__time-duration">${this.calculateTime(this.totalDuration)}</span>
								</div>
								<button class="ain-player__speed meta" aria-label="toggle playback speed"><span>1x</span></button>
							</div>
						</div>
					</div>
				</div>
			`
		}

		saveSelectors() {
			this.container 	= document.querySelector('.ain__container')
			this.element		= document.querySelector('.ain')
			this.cta 				= document.querySelector('.ain-player__cta')
			this.audio 			= document.getElementById('ain-track')
			this.player 		= document.querySelector('.ain-player')
			this.play 			= document.querySelector('.ain-player__play')
			this.rewind 		= document.querySelector('.ain-player__skip--bwd')
			this.forward 		= document.querySelector('.ain-player__skip--fwd')
			this.speedBtn		= document.querySelector('.ain-player__speed')
			this.volumeRng	= document.querySelector('.ain-player__volume__range-input')
			this.volumeBtn	= document.querySelector('.ain-player__volume__button')
			this.seek 			= document.querySelector('.ain-player__seek')
			this.played 		= document.querySelector('.ain-player__time-played')
		}

		setContainerHeight() {
			// so that the space is held when the player jumps down to fixed position on scroll
			this.container.style.minHeight = `${this.container.clientHeight}px`
		}

		initSequentialLoading() {
			// TO DO: wait to load the next clip until the current one is nearly done playing
			this.audio.addEventListener('progress', () => this.displayBuffered())
			this.audio.addEventListener('canplaythrough', () => {
				this.displayBuffered()
				this.preloadNextClip()
			})
			this.audio.addEventListener('ended', () => this.playNextClip())
		}

		preloadNextClip() {
			if (this.loadIdx < this.files.length - 1) {
				this.loadIdx++
				const preloading = new Audio()
				preloading.addEventListener('progress', () => this.displayBuffered(preloading, this.loadIdx))
				preloading.addEventListener('canplaythrough', () => {
					this.displayBuffered(preloading, this.loadIdx)
					this.preloadNextClip()
				})
				preloading.src = this.files[this.loadIdx]
			}
		}

		playNextClip() {
			if (this.playIdx < this.files.length - 1) {
				this.playIdx++
				this.audio.src = this.files[this.playIdx]
				this.audio.playbackRate = this.playRate
				this.audio.play()
			}
		}

		/************
		 * PLAYBACK *
		 ************/

		addPlaybackEventListeners() {
			this.cta.addEventListener('click', () => this.initialize())
			this.play.addEventListener('click', () => this.playing ? this.onPause() : this.onPlay())
			this.rewind.addEventListener('click', () => this.skipBack())
			this.forward.addEventListener('click', () => this.skipForward())
			this.speedBtn.addEventListener('click', () => this.togglePlaybackRate())
			this.volumeRng.addEventListener('input', (e) => this.adjustVolume(e))
			this.seek.addEventListener('input', (e) => this.onSliderInput(e.currentTarget.value))

			this.volumeBtn.addEventListener('click', () => {
				// on mobile, you need click to open the volume slider, so we don't want it to toggle mute, too
				if (window.matchMedia('(hover: hover)').matches) {
					this.toggleMute()
				} else {
					this.volumeBtn.parentElement.classList.toggle('active')
				}
			})
		}

		initialize() {
			this.active = true
			this.player.dataset.active = 'true'
			this.scrollObserver()
			this.onPlay()
		}

		onPlay() {
			this.audio.play()
			requestAnimationFrame(() => this.whilePlaying())
			this.playing = true
			this.player.dataset.play = 'play'

			if (this.audio.readyState > 0) {
				this.eventLog( 'audio_play' )
			} else {
				this.audio.addEventListener('loadedmetadata', () => this.eventLog( 'audio_play' ))
			}
		}

		onPause() {
			this.audio.pause()
			cancelAnimationFrame(this.raf)
			this.playing = false
			this.player.dataset.play = 'pause'
		}

		skipBack(offset = 15) {
			this.onSliderInput(this.totalPlay - offset)
		}

		skipForward(offset = 15) {
			this.onSliderInput(this.totalPlay + offset)
		}

		togglePlaybackRate() {
			const playRates = [.75, 1, 1.25, 1.5, 1.75, 2]
			const currentIdx = playRates.findIndex(pr => pr === this.playRate)
			const nextIdx = currentIdx < playRates.length - 1 ? currentIdx + 1 : 0

			this.playRate = playRates[nextIdx]
			this.audio.playbackRate = this.playRate
			this.speedBtn.innerHTML = `<span>${this.playRate}x</span>`
		}

		adjustVolume(e) {
			this.volume = parseInt(e.target.value)
			this.audio.volume = this.volume / 100
			this.player.style.setProperty('--volume', `${this.volume}%`)

			if (this.volume === 0) {
				this.mute()
			} else if (this.muted) {
				this.unmute()
			}
		}

		toggleMute() {
			if (this.muted === false) {
				this.mute()
				this.player.style.setProperty('--volume', '0%')
			} else {
				this.unmute()
				this.player.style.setProperty('--volume', `${this.volume}%`)
			}
		}

		mute() {
			this.audio.muted = true
			this.muted = true
			this.player.dataset.volume = 'off'
		}

		unmute() {
			this.audio.muted = false
			this.muted = false
			this.player.dataset.volume = 'on'
		}

		onSliderInput(seekTime) {
			if (seekTime === Math.floor(this.totalDuration)) {
				this.reset()
				return
			}

			let time = 0
			this.durations.forEach((length, idx) => {
				if (time + length < seekTime) {
					time += length
				} else {
					const audioTime = seekTime - time
					// if switching to a different audio file
					if (idx !== this.playIdx) {
						this.playedTime = this.files.map((t, idx2) => {
							return idx2 < idx ? this.durations[idx2] : (idx2 > idx ? 0 : audioTime)
						})
						this.playIdx = idx
						this.audio.src = this.files[idx]
					}
					this.audio.currentTime = audioTime
					this.audio.play()
				}
			})
			this.updateDisplay()
		}

		whilePlaying() {
			this.updateDisplay()
			this.trackProgress()
			this.raf = requestAnimationFrame(() => this.whilePlaying())
		}

		trackProgress() {
			if (this.nextMilestoneIdx < 100) {
				const milestone = this.audioMilestones[this.nextMilestoneIdx]
				if (this.totalPlay > milestone.timestamp) {
					const percentage_listened = milestone.percentage
					if (milestone.percentage < 90) {
						this.eventLog( 'audio_progress', { percentage_listened } )
						this.nextMilestoneIdx++
					} else {
						this.eventLog( 'audio_complete' )
						this.nextMilestoneIdx = 100
					}
				}
			}
		}

		reset() {
			this.active = false
			this.player.dataset.active = 'false'

			this.playIdx = 0
			this.audio.src = this.files[0]
			this.audio.currentTime = 0
			this.seek.value = 0

			this.updateDisplay()
			this.onPause()
			this.removeScrollObserver()
		}

		updateDisplay() {
			this.playedTime[this.playIdx] = Math.round(this.audio.currentTime)
			this.totalPlay = Math.floor(this.playedTime.reduce((total,num) => total + num), 0)

			this.displayTime()
			this.displayProgress()
		}

		displayTime() {
			this.played.textContent = this.calculateTime(this.totalPlay)
		}

		displayProgress() {
			this.seek.value = this.totalPlay
			const percent = this.totalPlay / this.totalDuration * 100
			this.player.style.setProperty('--played', `${percent}%`)
		}

		displayBuffered(src = this.audio, idx = this.playIdx) {
			if (src.buffered.length === 0) {
				return
			}

			const fileBufferedTime = Math.round(src.buffered.end(src.buffered.length - 1))
			this.bufferedTime[idx] = fileBufferedTime
			const totalBufferedTime = Math.round(this.bufferedTime.reduce((total,num) => total + num), 0)
			this.player.style.setProperty('--buffered', `${totalBufferedTime / this.totalDuration * 100}%`)
		}

		calculateTime(secs) {
			const minutes = Math.floor(secs / 60)
			const seconds = Math.floor(secs % 60)
			const returnedSeconds = seconds < 10 ? `0${seconds}` : `${seconds}`

			return `${minutes}:${returnedSeconds}`
		}

		calculateMinutes() {
			let minutes = Math.floor(this.totalDuration / 60)
			const hours = Math.floor(minutes / 60)

			let text
			if (hours > 0) {
				minutes = minutes % 60
				text = `${hours} ${hours > 1 ? 'hours' : 'hour'} ${minutes} ${minutes > 1 ? 'minutes' : 'minute'}`
			} else {
				text = `${minutes} ${minutes > 1 ? 'minutes' : 'minute'}`
			}

			return text
		}

		mediaSessionSetup() {
			if ('mediaSession' in navigator) {
				// metadata
				navigator.mediaSession.metadata = new MediaMetadata({
					title: this.data.title,
					artist: this.data.authors.join(', ')
				})

				// event listeners
				navigator.mediaSession.setActionHandler('play', () => this.onPlay())
				navigator.mediaSession.setActionHandler('pause', () => this.onPause())
				navigator.mediaSession.setActionHandler('stop', () => this.reset())
				navigator.mediaSession.setActionHandler('seekbackward', (details) => this.skipBack(details.seekOffset))
				navigator.mediaSession.setActionHandler('seekforward', (details) => this.skipForward(details.seekOffset))
				navigator.mediaSession.setActionHandler('seekto', (details) => {
					if (details.fastSeek && 'fastSeek' in audio) {
						this.audio.fastSeek(details.seekTime)
					} else {
						audio.currentTime = details.seekTime
					}
				})
			}
		}

		/*******
		 * ETC *
		 *******/

		scrollObserver() {
			const showDownpage = entries => {
				entries.forEach((entry) => {
					if (entry.intersectionRatio === 0) {
						this.element.classList.remove('ain--original')
						this.element.classList.add('ain--downpage')
					} else {
						this.element.classList.add('ain--original')
						setTimeout(() => {
							this.element.classList.remove('ain--downpage')
						}, 500)
					}
				})
			}
			this.observer = new IntersectionObserver((entries) => showDownpage(entries), { threshold: .001, rootMargin: '0px 0px' })
			this.observer.observe(this.container)

			// const footer = document.querySelector(this.data.config.footerSelector)
			// if (footer) {
			// 	const hideAtFooter = entries => {
			// 		entries.forEach((entry) => {
			// 			if (entry.intersectionRatio > 0) {
			// 				this.element.classList.add('ain--fadeout')
			// 				window.setTimeout(() => this.onPause(), 150)
			// 			} else {
			// 				this.element.classList.remove('ain--fadeout')
			// 			}
			// 		})
			// 	}
			// 	this.observer = new IntersectionObserver((entries) => hideAtFooter(entries), { threshold: .01, rootMargin: '0px 0px' })
			// 	this.observer.observe(footer)
			// }
		}

		removeScrollObserver() {
			this.observer.unobserve(this.container)
		}

		/*************
		 * ANALYTICS *
		 *************/

		// TO DO: move into core site repo
		eventLog( eventName, addtlParams = {} ) {
			const params = Object.assign({
				audio_title: this.data.title,
				audio_duration: Math.floor(this.totalDuration),
				audio_url: this.audio.src,
				type: 'ai-generated',
			}, addtlParams)
			gtag('event', eventName, params)
		}

		setAudioMilestones() {
			this.audioMilestones = [
				{
					timestamp: Math.floor(this.totalDuration * .05),
					percentage: 5
				},
				{
					timestamp: Math.floor(this.totalDuration * .25),
					percentage: 25
				},
				{
					timestamp: Math.floor(this.totalDuration * .5),
					percentage: 50
				},
				{
					timestamp: Math.floor(this.totalDuration * .7),
					percentage: 70
				},
				{
					timestamp: Math.floor(this.totalDuration * .9),
					percentage: 90
				},
			]
			this.nextMilestoneIdx = 0
		}
	}

	const AINarration = {
		init() {

		}
	}

	window.addEventListener('DOMContentLoaded', function() {
		if (window.AINarrationData) {
			window.ainPlayers = []
			ainPlayers.push(new AINPlayer(AINarrationData))
			document.body.classList.add('has-ai-narration')
		}
	})
}())