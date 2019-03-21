import { raport_to_bb } from '~/utils/helpers'

import 'core-js/fn/object/assign';
import 'core-js/fn/array/find';
import 'core-js/modules/es6.promise';
import 'core-js/modules/es6.array.iterator';
import 'core-js/modules/es6.array.find';
import closest from 'element-closest'

if (!process.server)
	closest(window)

window.raport_to_bb = raport_to_bb;

export default ({ store }) =>
{

}