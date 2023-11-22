import * as THREE from '../node_modules/three/build/three.module.js';
import WebGL from '../node_modules/three/examples/jsm/capabilities/WebGL.js';
import { OrbitControls } from '../node_modules/three/examples/jsm/controls/OrbitControls.js';
const BG_COLOR = 0xEEEEEE;
const WALL_COLOR = 0x808080;
const ITEM_COLOR = 0xCACACA;
const X_ARROW_COLOR = 0xfb9783;
const Y_ARROW_COLOR = 0x74f973;
const Z_ARROW_COLOR = 0x63a5fa;
const ARROW_SIZE = [0.2, 0.5, 8]
// Create a second cube for the outline
const OUTLINE_MATERIAL = new THREE.MeshBasicMaterial({ color: 0xff8c00, side: THREE.BackSide });
var OUTLINE_CUBE;
var selectedShape;

// Create a rotate button (cylinder)
const ROTATE_BUTTON_COLOR = 0xff8c00;
const ROTATE_BUTTON_RADIUS = 0.1;
const ROTATE_BUTTON_HEIGHT = 0.02;
const ROTATION_SPEED = 0.01;
var rotateButton;

const ALPHA = 0.2;


if ( WebGL.isWebGLAvailable() ) {
	var scene, selectedArrow, selectedRotate, camera, renderer, rect, controls, floor, leftWall, backWall, previousDirection;
	var filteredGradient = 0;
	var shapes_3D = [];
	init_3D();
	
	// Create arrows for X, Y, and Z axes
	var arrows = [];

	const raycaster = new THREE.Raycaster();
	const pointer = new THREE.Vector2();

	// Create a visual representation of the ray
	var rayLineMaterial = new THREE.LineBasicMaterial({ color: 0xff0000 });
	var rayLineGeometry = new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(), new THREE.Vector3()]);
	var rayLine = new THREE.Line(rayLineGeometry, rayLineMaterial);
	// scene.add(rayLine);

	// Variables to track drag state
	var isDragging = false;
	let startPosition = new THREE.Vector3();

	function onCTRL(event) {
		controls.enabled = !controls.enabled;
	}

	function onMouseDown( event ) {
		// calculate pointer position in normalized device coordinates
		// (-1 to +1) for both components
		pointer.x = ((event.clientX - rect.left) / renderer.domElement.clientWidth) * 2 - 1;
    	pointer.y = -((event.clientY - rect.top) / renderer.domElement.clientHeight) * 2 + 1;
		// update the picking ray with the camera and pointer position
		raycaster.setFromCamera(pointer, camera)
		// Visualize the ray
		var rayLinePoints = rayLine.geometry.attributes.position.array;
		rayLinePoints[0] = raycaster.ray.origin.x;
		rayLinePoints[1] = raycaster.ray.origin.y;
		rayLinePoints[2] = raycaster.ray.origin.z;
		rayLinePoints[3] = raycaster.ray.origin.x + raycaster.ray.direction.x * 100; // Adjust length as needed
		rayLinePoints[4] = raycaster.ray.origin.y + raycaster.ray.direction.y * 100;
		rayLinePoints[5] = raycaster.ray.origin.z + raycaster.ray.direction.z * 100;
		rayLine.geometry.attributes.position.needsUpdate = true;

		const arrow_intersects = raycaster.intersectObjects(arrows);
		if (arrow_intersects.length > 0) {
			isDragging = true;
			controls.enabled = false;
			selectedArrow = arrow_intersects[0].object;
			// Store the initial position of the mouse when dragging starts
			startPosition.copy(getMousePosition(event));
			return;
		}
		if (typeof rotateButton != "undefined") {
			const rotate_intersects = raycaster.intersectObject(rotateButton);
			if (rotate_intersects.length > 0) {
				isDragging = true;
				controls.enabled = false;
				selectedRotate = rotate_intersects[0].object;
				startPosition.copy(getMousePosition(event));
				return;
			}
		}

		// calculate objects intersecting the picking ray
		for (const shape_3D of shapes_3D) {
			const intersects = raycaster.intersectObject( shape_3D );
			if (intersects.length > 0) {
				removeLastSelectedShape(arrows);
				selectedShape = shape_3D;
				OUTLINE_CUBE = new THREE.Mesh(shape_3D.geometry.clone(), OUTLINE_MATERIAL);
				OUTLINE_CUBE.scale.set(1.05, 1.05, 1.05); // Slightly larger than the original cube
				OUTLINE_CUBE.position.copy(shape_3D.position);

				scene.add(OUTLINE_CUBE);
				arrows = createArrowsAxis(shape_3D)
				// Add rotate button near the origin (you can adjust the position)
  				rotateButton = addRotateButton(shape_3D, new THREE.Vector3(shape_3D.geometry.parameters.width/2, shape_3D.geometry.parameters.height/2, shape_3D.geometry.parameters.depth/2));
				for (const arrow of arrows) {
					scene.add(arrow);
				}
				// Set the drag state
				isDragging = true;
				controls.enabled = false;
				break;
			} else {
				controls.enabled = true;
				OUTLINE_CUBE.visible = false;
				for (const arrow of arrows) {
					scene.remove(arrow)
				}
				scene.remove(rotateButton)
			}
		}
	}

	// Handle mouse move events
	function onMouseMove(event) {
		// Check if dragging and controls are disabled
		if (isDragging && !controls.enabled && selectedShape) {
			// Calculate the current position of the mouse
			const currentPosition = getMousePosition(event);

			// Calculate the offset between the initial and current mouse positions
			const offset = currentPosition.clone().sub(startPosition);
			
			if (selectedArrow) {
				// Move the cube and all arrows based on the offset
				// Update the cube's position based on the arrow being dragged
				if (selectedArrow.direction.equals(new THREE.Vector3(-1, 0, 0))) {
					// X-axis arrow
					const newX = selectedShape.position.x + offset.x
					if (newX >= leftWall.position.x + leftWall.scale.x / 2) {
						selectedShape.position.x = newX;
						for (const arrow of arrows) {
							arrow.position.x += offset.x;
						}
						rotateButton.position.copy(selectedShape.position.clone().add(new THREE.Vector3(selectedShape.geometry.parameters.width/2, selectedShape.geometry.parameters.height/2, selectedShape.geometry.parameters.depth/2)));
					}
				} else if (selectedArrow.direction.equals(new THREE.Vector3(0, 1, 0))) {
					// Y-axis arrow
					const newY = selectedShape.position.y + offset.y;
					if (newY >= floor.position.y + floor.scale.y / 2) {
						selectedShape.position.y = newY;
						for (const arrow of arrows) {
							arrow.position.y += offset.y;
						}
						rotateButton.position.copy(selectedShape.position.clone().add(new THREE.Vector3(selectedShape.geometry.parameters.width/2, selectedShape.geometry.parameters.height/2, selectedShape.geometry.parameters.depth/2)));
					}		
				} else if (selectedArrow.direction.equals(new THREE.Vector3(0, 0, -1))) {
					// Z-axis arrow
					const newZ = selectedShape.position.z + offset.z;
					if (newZ >= backWall.position.z + backWall.scale.z / 2) {
						selectedShape.position.z = newZ;
						for (const arrow of arrows) {
							arrow.position.z += offset.z;
						}
						rotateButton.position.copy(selectedShape.position.clone().add(new THREE.Vector3(selectedShape.geometry.parameters.width/2, selectedShape.geometry.parameters.height/2, selectedShape.geometry.parameters.depth/2)));
					}				
				}
			} else if (selectedRotate) {
				// Calculate the direction of mouse movement
				const direction = new THREE.Vector2().subVectors(pointer, startPosition);
				// Calculate the gradient of the x-direction
				const gradient = previousDirection ? previousDirection.x - direction.x : 0;
				previousDirection = direction;
				// Apply the low-pass filter
				filteredGradient = ALPHA * gradient + (1 - ALPHA) * filteredGradient;

				// Determine the rotation direction based on the sign of the gradient
				const rotationDirection = Math.sign(filteredGradient);

				const deltaRotationQuaternion = new THREE.Quaternion().setFromEuler(
					new THREE.Euler(0, rotationDirection * Math.PI * 2 * ROTATION_SPEED, 0, 'XYZ')
				  );
				selectedShape.quaternion.multiplyQuaternions(deltaRotationQuaternion, selectedShape.quaternion);
				OUTLINE_CUBE.quaternion.multiplyQuaternions(deltaRotationQuaternion, selectedShape.quaternion);

				// Update arrowX and arrowZ positions as the shape rotates
				const arrowXOffset = new THREE.Vector3(1, 0, 0).applyQuaternion(selectedShape.quaternion);
				const arrowZOffset = new THREE.Vector3(0, 0, -1).applyQuaternion(selectedShape.quaternion);
			
				arrows[0].position.copy(selectedShape.position.clone().add(arrowXOffset));
				arrows[2].position.copy(selectedShape.position.clone().add(arrowZOffset));

				// Calculate the position of the rotate button as it revolves around the center of the shape
				const rotateButtonRadius = selectedShape.geometry.parameters.width/2; // Adjust the radius as needed
				const rotateButtonAngle = rotationDirection * Math.PI * 2 * ROTATION_SPEED;
				const rotateButtonPosition = new THREE.Vector3(
				  rotateButtonRadius * Math.cos(rotateButtonAngle),
				  0,
				  rotateButtonRadius * Math.sin(rotateButtonAngle)
				).applyQuaternion(selectedShape.quaternion);
				
				// Set the rotation of the rotate button to zero
				rotateButton.rotation.set(Math.PI/2, 0, 0);

				// Shift the rotate button to one of the top corners
				const topCornerOffset = new THREE.Vector3(0, selectedShape.geometry.parameters.height / 2, selectedShape.geometry.parameters.depth / 2)
				.applyQuaternion(selectedShape.quaternion);
		  
				// Update the position of the rotate button
				rotateButton.position.copy(selectedShape.position.clone().add(rotateButtonPosition).add(topCornerOffset));

				// Calculate the position of the rotate button as it revolves around the center of the shape
				const arrowXRadius = selectedShape.geometry.parameters.width/2; // Adjust the radius as needed
				const arrowXAngle = rotationDirection * Math.PI * 2 * ROTATION_SPEED;
				const arrowXPosition = new THREE.Vector3(
					arrowXRadius * Math.cos(arrowXAngle),
				  0,
				  arrowXRadius * Math.sin(arrowXAngle)
				).applyQuaternion(selectedShape.quaternion);
				arrows[0].rotation.set(0, 0, -Math.PI/2)
				// Update the position of the rotate button
				arrows[0].position.copy(selectedShape.position.clone().add(arrowXPosition));
		  
			}
	
			// Update the initial mouse position for the next movement
			startPosition.copy(currentPosition);
			// Update the outline cube's position based on the cube being dragged
			OUTLINE_CUBE.position.copy(selectedShape.position);	
		}
	}

	// Helper function to get the mouse position in world coordinates
	function getMousePosition(event) {
		pointer.x = ((event.clientX - rect.left) / renderer.domElement.clientWidth) * 2 - 1;
		pointer.y = -((event.clientY - rect.top) / renderer.domElement.clientHeight) * 2 + 1;

		raycaster.setFromCamera(pointer, camera);
		const intersection = new THREE.Vector3();
		raycaster.ray.intersectPlane(new THREE.Plane().setFromNormalAndCoplanarPoint(camera.getWorldDirection(intersection), selectedShape.position), intersection);

		return intersection;
	}
	// Handle mouse up events
	function onMouseUp() {
		// Reset drag state and re-enable controls
		isDragging = false;
		controls.enabled = true;
		selectedArrow = null;
	}

	window.addEventListener( 'mousedown', onMouseDown, false );
	window.addEventListener( 'mousemove', onMouseMove, false );
	window.addEventListener( 'mouseup', onMouseUp, false );
	
	camera.position.set(0, 3, 5);
	camera.lookAt(0, 0, 0);
	for (const arrow of arrows) {
		arrow.cursor = 'grab';
	}
	function animate() {
		requestAnimationFrame( animate );
		controls.update();
		renderer.render( scene, camera );
	}
	animate();

	

} else {

	const warning = WebGL.getWebGLErrorMessage();
	document.getElementById('three_d_container').appendChild( warning );

}


function init_3D() {
	scene = new THREE.Scene();
	const scene_bg = new THREE.Color( BG_COLOR )
	const container_width = window.innerWidth - document.getElementById("sidebar").clientWidth - parseInt($(document.getElementById("base_container")).css('padding-left')) - parseInt($(document.getElementById("base_container")).css('padding-right'))
	scene.background = scene_bg
	
	camera = new THREE.PerspectiveCamera( 75, container_width / window.innerHeight, 1, 1000 );
	renderer = new THREE.WebGLRenderer();
	renderer.setSize( container_width, window.innerHeight );
	document.getElementById('three_d_container').appendChild( renderer.domElement );
	rect = renderer.domElement.getBoundingClientRect();
	// Set up controls
	controls = new OrbitControls(camera, renderer.domElement);
	controls.enableDamping = true; // an animation loop is required when damping is enabled
	controls.dampingFactor = 0.25;
	controls.screenSpacePanning = false;
	controls.maxPolarAngle = Math.PI/3;
	controls.mouseButtons = {
		LEFT: THREE.MOUSE.ROTATE,
		MIDDLE: THREE.MOUSE.PAN, // Set the middle mouse button to pan
		RIGHT: THREE.MOUSE.DOLLY,
	  };


	// Add floor
	var floorGeometry = new THREE.PlaneGeometry(10, 10, 10, 10);
	var floorMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff, wireframe: true });
	floor = new THREE.Mesh(floorGeometry, floorMaterial);
	floor.rotation.x = -Math.PI / 2; // Rotate to be horizontal
	scene.add(floor);

	// Add left wall
	leftWall = addWall([0.1, 2, 10], WALL_COLOR, [-5, 1, 0])
	backWall = addWall([10, 2, 0.1], WALL_COLOR, [0, 1, -5])
}

function addWall(geometry, color, position) {
	var wallGeometry = new THREE.BoxGeometry(geometry[0], geometry[1], geometry[2]);
	var wallMaterial = new THREE.MeshBasicMaterial({ color: color });
	var wall = new THREE.Mesh(wallGeometry, wallMaterial);
	wall.position.set(position[0], position[1], position[2]);
	scene.add(wall);

	return wall;
}

function createArrowsAxis(shape_3D) {
	const arrowX = addArrow(ARROW_SIZE, X_ARROW_COLOR, shape_3D, [shape_3D.geometry.parameters.width/2, 0, 0], [0, 0, -Math.PI/2], [-1, 0, 0])
	const arrowY = addArrow(ARROW_SIZE, Y_ARROW_COLOR, shape_3D, [0, shape_3D.geometry.parameters.height/2, 0], [0, 0, 0], [0, 1, 0])
	const arrowZ = addArrow(ARROW_SIZE, Z_ARROW_COLOR, shape_3D, [0, 0, shape_3D.geometry.parameters.depth/2], [Math.PI/2, 0, 0], [0, 0, -1])
	return [arrowX, arrowY, arrowZ]
}

function addRotateButton(shape_3D, position) {
	const buttonGeometry = new THREE.CylinderGeometry(ROTATE_BUTTON_RADIUS, ROTATE_BUTTON_RADIUS, ROTATE_BUTTON_HEIGHT, 32);
	const buttonMaterial = new THREE.MeshBasicMaterial({ color: ROTATE_BUTTON_COLOR });
	const rotateButton = new THREE.Mesh(buttonGeometry, buttonMaterial);
	rotateButton.position.copy(shape_3D.position.clone().add(position));
	rotateButton.rotation.x = Math.PI / 2; // Orient the cylinder upright
	scene.add(rotateButton);

	return rotateButton
  }

function addArrow(geometry, color, shape_3D, position, rotation, direction) {
	// Arrow for X-axis
	const arrowGeometry = new THREE.ConeGeometry(geometry[0], geometry[1], geometry[2]);
	const arrowMaterial = new THREE.MeshBasicMaterial({ color: color });
	const arrow = new THREE.Mesh(arrowGeometry, arrowMaterial);
	arrow.position.copy(shape_3D.position.clone().add(new THREE.Vector3(position[0], position[1], position[2])));
	arrow.rotation.set(rotation[0], rotation[1], rotation[2]); // Adjust rotation to face left
	arrow.direction = new THREE.Vector3(direction[0], direction[1], direction[2]); // Adjust direction to face left
	// scene.add(arrowX);
	return arrow
}

function removeLastSelectedShape(arrows) {
	scene.remove(OUTLINE_CUBE)
	for (const arrow of arrows) {
		scene.remove(arrow)
	}
}

export function addShapeThreeD(shape) {
	const geometry = new THREE.BoxGeometry( shape.length/500, shape.width/500, shape.height/500 );
	const material = new THREE.MeshBasicMaterial( { color: ITEM_COLOR } );
	const shape_3D = new THREE.Mesh( geometry, material );
	scene.add( shape_3D );
	shape_3D.position.y = shape.width/500/2;
	shape_3D.position.x = shape.x/50;
	shape_3D.position.z = shape.y;
	shapes_3D.push(shape_3D)
}