import Options from './Options';
const { Component } = wp.element;

class Rationale extends Component {
    state = {
        error: undefined,
        formSubmitted: false
    };
    /**
     * handleRationaleObj: cleans the user's rationale and updates state
     * Parameter: e, the user's input
     * Fires: when the user presses 'Enter Rationale'
     */
    handleRationaleObj = (e) => {
        // e is event object
        e.preventDefault();

        const rationale = e.target.elements.rationale.value.trim();
        const error = this.props.handleRationale(rationale);

        this.setState(() => ({
            error: error,
            formSubmitted: true
        }));
    };

    handleNextHere = () => {
        this.setState(() => ({formSubmitted: false}));
        this.props.handleNext();
    }

    render() {
        return (
            <div id="rationale">
                {this.state.error && <p>{this.state.error}</p>}
                {!this.state.formSubmitted && 
                    <form onSubmit={this.handleRationaleObj}>
                        <span>Please explain the judgment for this response:</span>
                        <textarea name="rationale" cols={40} rows={5} maxLength={1000} required placeholder={"125 words or less"} />
                        <br />
                        <br />
                        <Options 
                            handleChoice={this.props.handleChoice}
                            levelTitles={this.props.levelTitles}
                        />
                    </form>
                }
                {this.state.formSubmitted && <button onClick={this.handleNextHere}>Next</button>}
            </div>
        );
    }
}

export default Rationale;