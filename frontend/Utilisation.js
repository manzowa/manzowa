import Can from "../../auth/Can";
import { PERMISSIONS } from "../../config/permissions";

<Can permission={PERMISSIONS.CREATE_EVENT}>
  <button>Créer un événement</button>
</Can>
