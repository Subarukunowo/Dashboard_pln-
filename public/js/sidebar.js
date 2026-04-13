document.addEventListener('DOMContentLoaded', function () {
    const items    = document.querySelectorAll('.sidebar-item')
    const sections = document.querySelectorAll('.dash-section')
    const toggle   = document.getElementById('sidebarToggle')
    const sidebar  = document.getElementById('dashSidebar')
    const content  = document.querySelector('.dash-content')

    // Mobile toggle
    toggle?.addEventListener('click', () => sidebar.classList.toggle('open'))

    // Close sidebar saat click di luar (mobile)
    document.addEventListener('click', e => {
        if (window.innerWidth <= 900 &&
            !sidebar.contains(e.target) &&
            e.target !== toggle) {
            sidebar.classList.remove('open')
        }
    })

    // Section switching
    items.forEach(item => {
        item.addEventListener('click', function () {
            const target = this.dataset.section

            items.forEach(i => i.classList.remove('active'))
            this.classList.add('active')

            if (target === 'all') {
                sections.forEach(s => s.classList.add('active'))
            } else {
                sections.forEach(s => {
                    s.classList.toggle('active', s.dataset.sectionContent === target)
                })
            }

            if (window.innerWidth <= 900) sidebar.classList.remove('open')
            content?.scrollTo({ top: 0, behavior: 'smooth' })
        })
    })
})