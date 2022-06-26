class Footer extends Component {
    build(parent) {}
    constructor(app) {
        super(app, COMPONENT_TYPE_FOOTER);
    }
}

class Header extends Component {
    get raw() {
        return `
        <header class="sticky-top">
            <nav class="navbar navbar-dark bg-dark">
                <div class="px-5 d-flex flex-row gap-5 w-100">
                    <ns-a class="c-nav-link" aria-current="page" href="/" aria-label="home" title="Home"><i class="bi bi-house"></i></ns-a>
                    <ns-a class="c-nav-link" href="/event" aria-label="switch" title="Switch"><i class="bi bi-book"></i></ns-a>
                    <ns-a class="c-nav-link" href="/volume" aria-label="device" title="Device"><i class="bi bi-file-earmark-break"></i></ns-a>
                    <ns-a class="c-nav-link" href="/image" aria-label="user" title="User"><i class="bi bi-image"></i></ns-a>
                    <ns-a class="c-nav-link" href="/users" aria-label="archives" title="Archives"><i class="bi bi-person"></i></ns-a>
                    <ns-a class="c-nav-link" href="/log-root" aria-label="log" title="Log"><i class="bi bi-journal"></i></ns-a>
                </div>
            </nav>
        </header>
        `;
    }

    getHTML() {
        return super.getHTML();
    }

    build(parent) {
        super.build(parent);
    }

    constructor(app) {
        super(app, COMPONENT_TYPE_FOOTER);
    }

}

class Index extends Pages {

    get raw() {
        return `
        <section class="indexS">
            <ns-api-data-block class="part-container" href="admin/stats">
                <div class="box">
                    <span>
                    
                    </span>
                    <ns-a class="content" href="project">
                        <i class="bi bi-book"></i>
                        <ns-api-data field="project">0</ns-api-data>
                        <h3>Projet</h3>
                    </ns-a>
                </div>
                <div class="box" style="--color-one: #4ca5ad; --color-two: #1d8cd1">
                    <span>
                    
                    </span>
                    <ns-a class="content" href="volume">
                        <i class="bi bi-file-earmark-break"></i>
                        <ns-api-data field="volume">0</ns-api-data>
                        <h3>Volumes</h3>
                    </ns-a>
                </div>
                <div class="box" style="--color-one: #70ad4c; --color-two: #65d11d">
                    <span>
                    
                    </span>
                    <ns-a class="content" href="device">
                        <i class="bi bi-image"></i>
                        <ns-api-data field="picture">0</ns-api-data>
                        <h3>Image</h3>
                    </ns-a>
                </div>
                <div class="box" style="--color-one: #4C6CAD; --color-two: #1D59D1">
                    <span>
                    
                    </span>
                    <ns-a class="content" href="/users">
                        <i class="bi bi-person"></i>
                        <ns-api-data field="user">0</ns-api-data>
                        <h3>Utilisateurs</h3>
                    </ns-a>
                </div>
                 <div class="box" style="--color-one: #ad4ca0; --color-two: #d11dad">
                    <span>
                    
                    </span>
                    <ns-a class="content" href="/log-root">
                        <i class="bi bi-journal"></i>
                        <ns-api-data field="log-root">0</ns-api-data>
                        <h3>Log</h3>
                    </ns-a>
                </div>
            </ns-api-data-block>
        </section>
        `;
    }

    build(parent, vars) {
        super.build(parent, vars);
    }

    constructor(app) {
        super(app, false, false);
    }
}

const STRUCTURE = [
    {
        re: /^(|index|home)$/,
        rel: "index"
    },
    {
        re: /^event$/,
        rel: "admin/event"
    },
    {
        re: /^log-root$/,
        rel: 'admin/log_root'
    },
    {
        re: /^project$/,
        rel: "admin/project"
    },
    {
        re: /^volume(?:\/(.*))?$/,
        var: [{id: 1, name: 'project'}],
        rel: "admin/volume"
    },
    {
        re: /^users$/,
        rel: "admin/users"
    }
]

export const APP = new Application(Header, Footer, Index, Error404, STRUCTURE, 'admin');
window.APP = APP;
APP.addEventListener('log', e => {
    console.log(e);
    if (APP.user.permissionLevel < 200) document.location.href = '/';
})
APP.addEventListener('logout', e => {
    console.log(e);
    document.location.href = '/';
});
APP.start();
