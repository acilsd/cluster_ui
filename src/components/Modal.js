import React, { PropTypes, Component }  from 'react';
import ReactDOM                         from 'react-dom';
import getScrollbarSize                 from 'dom-helpers/util/scrollbarSize';
import canUseDOM                        from 'dom-helpers/util/inDOM';
import ownerDocument                    from 'dom-helpers/ownerDocument';
import isOverflowing                    from 'react-overlays/lib/utils/isOverflowing';
import { getClass }                     from 'utils/ClassName';

/**
 * Обертка для модального окна.
 */
const Modal = ({ modal, size, modifier, children, hideModal, onUnmount }) => {
  /**
   * Если модификатор не равен параметру name из стора,
   * значит модальное окно не открыто
   */
  if (modifier !== modal.name) {
    return null;
  }

  return (
    <ModalWindow
      size={size}
      modifier={modifier}
      children={children}
      hideModal={hideModal}
      onUnmount={onUnmount}
    />
  );
};

Modal.propTypes = {
  size: PropTypes.string,
  modifier: PropTypes.string.isRequired,
};

Modal.defaultProps = {
  onUnmount() {},
};

class ModalWindow extends Component {
  constructor() {
    super();

    this.hideModal          = this.hideModal.bind(this);
    this.closeOnEsc         = this.closeOnEsc.bind(this);
    this.handleWindowResize = this.handleWindowResize.bind(this);

    this.state = {
      styles: {
        paddingRight: 0,
        paddingLeft: 0,
      },
    };
  }

  componentDidMount() {
    window.addEventListener('resize', this.handleWindowResize, false);
    document.addEventListener('keyup', this.closeOnEsc, false);
    document.body.classList.add('modal-opened');

    this.handleWindowResize();
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.handleWindowResize, false);
    document.removeEventListener('keyup', this.closeOnEsc, false);
    document.body.classList.remove('modal-opened');

    this.props.onUnmount();
  }

  hideModal() {
    this.props.hideModal();
  }

  handleWindowResize() {
    this.setState({
      styles: this.getStyles(),
    });
  }

  getStyles() {
    if (!canUseDOM) {
      return {};
    }

    const home = ReactDOM.findDOMNode(this._modal);
    const doc = ownerDocument(home);

    const scrollHeight = home.scrollHeight;
    const bodyIsOverflowing = isOverflowing(ReactDOM.findDOMNode(this.props.container || doc.body));
    const modalIsOverflowing = scrollHeight > doc.documentElement.clientHeight;

    const paddingRight = bodyIsOverflowing && !modalIsOverflowing ? getScrollbarSize() : 0;
    const paddingLeft = !bodyIsOverflowing && modalIsOverflowing ? getScrollbarSize() : 0;

    return { paddingRight, paddingLeft };
  }

  closeOnEsc(e) {
    /**
     * 27 - ESC button
     */
    if (e.keyCode === 27) {
      this.hideModal();
    }
  }

  render() {
    const { size, modifier, children } = this.props;
    return (
      <div className='modal-outer' onClick={this.hideModal} style={this.state.styles} ref={ref => this._modal = ref}>
        <div className='modal-middle'>
          <div className={''} onClick={e => e.stopPropagation()}>
            {children}
          </div>
        </div>
      </div>
    );
  }
}

Modal.Icon = ({ modifiers, children }) => (
  <div className={getClass('modal__icon', modifiers)}>
    {children}
  </div>
);

Modal.Footer = ({ modifiers, children }) => (
  <div className={getClass('modal__footer', modifiers)}>
    {children}
  </div>
);

export default Modal;
