class ModalEditStatus extends Component {

    id;

    get raw() {
        return `
<form id="nsa-modal-pform">
    <h3>Changer le staus du projet n° ${this.id} ? </h3>
    <div class="ns-form-group">
    <input type="hidden" hidden="hidden" name="project" value="${this.id}">
    <label for="nsa-mdoal-ps" class="form-label">Status</label>
        <select id="nsa-mdoal-ps" name="status" class="form-select">
            <option value="0">Attente de vérification</option>   
            <option value="1" >Rejeté</option>   
            <option value="2" selected>Accepté en attente de contenue</option>   
            <option value="3">Publié</option>   
        </select>     
    </div>
    <div class="ns-form-group">
        <label for="nsa-mdoal-pr" class="form-label">Raison</label>
        <textarea id="nsa-mdoal-pr" name="reason" rows="4" maxlength="255" class="form-control"></textarea>
        <div class="form-text">Raison de la modification</div>
    </div>
    <div class="fpp-modal-btn-container">
        <button type="button" class="ns-modal-cancel-btn bg-secondary"> Annuler </button>
        <button type="submit" class="bg-danger">Modifier</button>
    </div>
           
</form>`;
    }

    build(parent) {
        super.build(parent);
        _('#nsa-modal-pform').addEventListener('submit', this.sendRequest.bind(this));

    }

    constructor(app, id) {
        super(app, COMPONENT_TYPE_MODAL);
        this.id = id;
    }

    sendRequest(event) {
        event.preventDefault();
        loadingScreen(true);
        this.app.closeModal();
        sendApiPostRequest('project/validation', new FormData(event.target), (e) => {
            loadingScreen(false);
        })
    }
}

export default class extends SimpleTablePages {

    COLUMN = [
        {name: 'project', display: 'Projet', force: true, default: 'null', isDefault: true},
        {name: 'volume', display: 'Tome', force: true, default: 'null', isDefault: true},
        {name: 'author', display: 'Auteur', force: false, default: 'null', isDefault: true, href: ''},
        {name: 'picture', display: 'Vignette', force: false, default: 'null', isDefault: true, href: ''},
        {name: 'title', display: 'Titre', force: false, default: 'null', isDefault: true},
        {name: 'data', display: 'Data', force: false, default: 'null', isDefault: true,  isPrimary: true},
        {name: 'status', display: 'Status', force: false, default: '0', needCallback: true,  isDefault: true},
        {name: 'date_inserted', display: 'Créé le', force: false, default: 'never', isDefault: true}
    ];

    constructor(app) {
        super(app);
        this.setTable(new AdminTable(app, 'project', this.COLUMN, 'project/volumes-all', this.collCallback.bind(this)));
    }

    collCallback(name, e, value, rowData) {
        switch (name) {
            case 'status':
                e.innerHTML = project_status_to_html(value);
                break;
            case 'action':
                const group = create('div', null, e, 'btn-group');
                group.role = 'group';
                group.ariaLabel = 'Action button';
                createPromise('button', null, group, 'btn', 'btn-info', 'btn-sm').then((btn) => {
                    btn.ariaLabel = 'Edit';
                    btn.setAttribute('data-bs-toggle', 'tooltip');
                    btn.title = 'Modifier le status';
                    btn.type = "button";
                    btn.addEventListener('click', this.openChangeStatusModal.bind(this, rowData['id']));
                    create('i', null, btn, 'bi', 'bi-pencil');
                });
                break;
        }
    }


    build(parent, vars) {
        super.build(parent, vars);
        if (vars["project"])
            this.admTable.vars = 'project=' + vars["project"];
    }

    openChangeStatusModal(id) {
        this.app.openModal(new ModalEditStatus(this.app, id));
    }

}